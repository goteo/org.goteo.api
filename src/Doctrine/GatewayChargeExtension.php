<?php

namespace App\Doctrine;

use App\Entity\Gateway\Charge;
use App\Entity\User\User;
use Doctrine\ORM\QueryBuilder;

final class GatewayChargeExtension extends AbstractGatewayExtension
{
    protected function getEntityClass(): string
    {
        return Charge::class;
    }

    protected function applyFilters(QueryBuilder $queryBuilder, string $rootAlias, User $user): void
    {
        $queryBuilder
            ->leftJoin("$rootAlias.checkout", 'c')
            ->leftJoin('c.origin', 'co')
            ->leftJoin('co.user', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId());
    }
}
