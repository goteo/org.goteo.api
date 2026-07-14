<?php

namespace App\State\Gateway;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Exception\RuntimeException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Gateway\ChargeStats;
use App\Entity\Gateway\Charge;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Provides the number of distinct Projects targeted by a filtered GatewayCharge collection.
 *
 * Reuses the same ApiFilters declared on the GatewayCharge collection (target, status,
 * type, money range, dates, ...) by re-applying every registered ORM collection
 * extension, then runs a single COUNT(DISTINCT project) query.
 */
class ChargeStatsStateProvider implements ProviderInterface
{
    /**
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        private readonly iterable $collectionExtensions,
        private ManagerRegistry $managerRegistry,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ChargeStats
    {
        $manager = $this->managerRegistry->getManagerForClass(Charge::class);

        $repository = $manager->getRepository(Charge::class);
        if (!method_exists($repository, 'createQueryBuilder')) {
            throw new RuntimeException('The repository class must have a "createQueryBuilder" method.');
        }

        $queryBuilder = $repository->createQueryBuilder('o');
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, Charge::class, $operation, $context);
        }

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
