<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Kyzegs\DoctrineEncryptionBundle\Attribute\BlindIndex;
use Kyzegs\DoctrineEncryptionBundle\Hashers\BlindIndexHasherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class EncryptedSearchFilter extends AbstractFilter
{
    public function __construct(
        private BlindIndexHasherInterface $blindIndexHasher,
        #[Autowire(service: 'security.expression_language')]
        private ExpressionLanguage $expressionLanguage,
        private Security $security,
        protected ?ManagerRegistry $managerRegistry,
        ?LoggerInterface $logger = null,
        protected ?array $properties = null,
        protected ?NameConverterInterface $nameConverter = null,
        private string $normalizer = BlindIndex::NORMALIZE_UPPERCASE,
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (
            !$this->isPropertyEnabled($property, $resourceClass)
            || !$this->isPropertyMapped($property, $resourceClass)
            || !$this->isPropertyGranted($property, $resourceClass, $context)
        ) {
            return;
        }

        $values = (array) $value;
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $parameterName = ':'.$queryNameGenerator->generateParameterName($property);

        $alias = $rootAlias;
        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field] = $this->addJoinsForNestedProperty($property, $rootAlias, $queryBuilder, $queryNameGenerator, $resourceClass, Join::LEFT_JOIN);
        }

        $aliasedField = \sprintf('%s.%sLookup', $alias, $field);

        if (\count($values) > 1) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->in($aliasedField, $parameterName))
                ->setParameter($parameterName, array_map(fn($v) => $this->getHashLookup($v), $values));

            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->expr()->eq($aliasedField, $parameterName))
            ->setParameter($parameterName, $this->getHashLookup($values[0]));
    }

    private function getHashLookup(mixed $value): string
    {
        return $this->blindIndexHasher->hash($value, $this->normalizer);
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->properties as $property => $strategy) {
            $description[$property] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'is_collection' => false,
            ];

            $description[$property.'[]'] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'is_collection' => true,
            ];
        }

        return $description;
    }

    private function isPropertyGranted(string $property, string $resourceClass, array $context): bool
    {
        if ($this->isPropertyNested($property, $resourceClass)) {
            $propertyParts = $this->splitPropertyParts($property, $resourceClass);
            $metadata = new \ReflectionProperty($context['resource_class'], $propertyParts['associations'][0]);
        } else {
            $metadata = new \ReflectionProperty($context['resource_class'], $property);
        }

        foreach ($metadata->getAttributes(ApiProperty::class) as $attribute) {
            $security = $attribute->getArguments()['security'] ?? null;

            if ($security === null) {
                continue;
            }

            return $this->expressionLanguage->evaluate(
                $security,
                [
                    'user' => $this->security->getUser(),
                    'auth_checker' => $this->security,
                ]
            );
        }

        return true;
    }
}
