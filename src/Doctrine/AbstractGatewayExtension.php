<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User\User;

abstract class AbstractGatewayExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(protected Security $security) {}

    abstract protected function getEntityClass(): string;
    abstract protected function applyFilters(QueryBuilder $queryBuilder, string $rootAlias, User $user): void;

    private function addFilter(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== $this->getEntityClass()) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        if ($user->hasRoles(['ROLE_ADMIN'])) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $this->applyFilters($queryBuilder, $rootAlias, $user);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?\ApiPlatform\Metadata\Operation $operation = null,
        array $context = [],
    ): void {
        $this->addFilter($queryBuilder, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?\ApiPlatform\Metadata\Operation $operation = null,
        array $context = [],
    ): void {
        $this->addFilter($queryBuilder, $resourceClass);
    }
}
