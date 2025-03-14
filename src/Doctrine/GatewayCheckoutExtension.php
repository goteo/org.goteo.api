<?php

namespace App\Doctrine;

use App\Entity\Gateway\Checkout;
use App\Entity\User\User;
use Doctrine\ORM\QueryBuilder;

final class GatewayCheckoutExtension extends AbstractGatewayExtension
{
    protected function getEntityClass(): string
    {
        return Checkout::class;
    }

    protected function applyFilters(QueryBuilder $queryBuilder, string $rootAlias, User $user): void
    {
        $queryBuilder
            ->leftJoin("$rootAlias.origin", 'co')
            ->leftJoin('co.user', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId());
    }
}
