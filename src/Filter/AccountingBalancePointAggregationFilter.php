<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Money;
use App\Service\AccountingService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class AccountingBalancePointAggregationFilter extends AbstractFilter
{
    public function __construct(
        protected ?ManagerRegistry $managerRegistry,
        private AccountingService $accountingService,
        ?LoggerInterface $logger = null,
        protected ?array $properties = null,
        protected ?NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    /**
     * Filter the properties and add the accumulated balances.
     */
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
            || !$value['aggregate'] ?? false
        ) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $accountingId = $value['accounting'] ?? null;
        $start = $value['start'] ?? null;
        $end = $value['end'] ?? null;
        $interval = $value['interval'] ?? '24h';

        $balancePoints = $this->accountingService->calcBalancePoints(
            $accountingId,
            new \DatePeriod(new \DateTimeImmutable($start), new \DateInterval($interval), new \DateTimeImmutable($end))
        );

        $aggregatedBalance = 0;
        foreach ($balancePoints as $point) {
            $aggregatedBalance += $point->balance->amount ?? 0;
            $point->balance = new Money($aggregatedBalance, $point->balance->currency);
        }

        $parameterName = $queryNameGenerator->generateParameterName('id');
        $queryBuilder
            ->andWhere(sprintf('%s.accounting = :%s', $rootAlias, $parameterName))
            ->setParameter($parameterName, $accountingId);
    }

    /**
     * Returns the description of the properties that can be filtered.
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["$property"] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_ARRAY,
                'required' => false,
                'description' => 'Filters and aggregates balance points.',
            ];
        }

        return $description;
    }
}
