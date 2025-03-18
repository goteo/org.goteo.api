<?php

namespace App\Doctrine;

use App\Entity\Gateway\Charge;
use App\Security\Trait\UserTrait;
use Doctrine\ORM\QueryBuilder;

final class GatewayChargeExtension extends AbstractQueryResourceExtensionInterface
{
    use UserTrait;

    protected function supports(string $resourceClass): bool
    {
        return $resourceClass === Charge::class;
    }

    protected function applyFilters(QueryBuilder $queryBuilder, string $rootAlias): void
    {
        $user = $this->getAuthenticatedUser();

        if ($user == null) {
            return;
        }

        if ($this->isAdmin($user)) {
            return;
        }

        $queryBuilder
            ->leftJoin("$rootAlias.checkout", 'c')
            ->leftJoin('c.origin', 'co')
            ->leftJoin('co.user', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId());
    }
}
