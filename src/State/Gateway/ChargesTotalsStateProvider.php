<?php

namespace App\State\Gateway;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Gateway\ChargesTotalsDto;
use App\State\QueryBuilderExtractor;

/**
 * Provides totalized metrics for a filtered GatewayCharge collection.
 *
 * Reuses the same ApiFilters declared on the GatewayCharge collection (target, status,
 * type, money range, dates, ...) by re-applying every registered ORM collection
 * extension, then runs a single COUNT(DISTINCT project) query.
 */
class ChargesTotalsStateProvider implements ProviderInterface
{
    public function __construct(
        private QueryBuilderExtractor $queryBuilderExtractor,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ChargesTotalsDto
    {
        $queryBuilder = $this->queryBuilderExtractor->getQueryBuilder($operation, $context);

        $count = (int) $queryBuilder
            ->resetDQLPart('orderBy')
            ->join('o.target', 'totals_target')
            ->join('totals_target.project', 'totals_project')
            ->select('COUNT(DISTINCT totals_project.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return new ChargesTotalsDto($count);
    }
}
