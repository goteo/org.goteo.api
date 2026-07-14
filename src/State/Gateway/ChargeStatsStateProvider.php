<?php

namespace App\State\Gateway;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Gateway\ChargeStats;
use App\State\QueryBuilderExtractor;

/**
 * Provides the number of distinct Projects targeted by a filtered GatewayCharge collection.
 *
 * Reuses the same ApiFilters declared on the GatewayCharge collection (target, status,
 * type, money range, dates, ...) by re-applying every registered ORM collection
 * extension, then runs a single COUNT(DISTINCT project) query.
 */
class ChargeStatsStateProvider implements ProviderInterface
{
    public function __construct(
        private QueryBuilderExtractor $queryBuilderExtractor,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ChargeStats
    {
        $queryBuilder = $this->queryBuilderExtractor->getQueryBuilder($operation, $context);

        $count = (int) $queryBuilder
            ->resetDQLPart('orderBy')
            ->join('o.target', 'stats_target')
            ->join('stats_target.project', 'stats_project')
            ->select('COUNT(DISTINCT stats_project.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return new ChargeStats($count);
    }
}
