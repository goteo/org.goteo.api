<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractQueryResourceExtensionInterface implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /**
     * Determine if the extension is compatible with the given resource.
     */
    abstract protected function supports(string $resourceClass): bool;

    /**
     * Apply the filters to the query builder.
     */
    abstract protected function applyFilters(QueryBuilder $queryBuilder, string $rootAlias): void;

    private function addFilter(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (!$this->supports($resourceClass)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $this->applyFilters($queryBuilder, $rootAlias);
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
