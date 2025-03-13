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
            ->leftJoin("$rootAlias.checkout", 'c') // Relación con GatewayCheckout
            ->leftJoin('c.origin', 'co') // Relación con Accounting
            ->leftJoin('co.user', 'u') // Relación con User
            ->andWhere('u.id = :userId') // Filtrar por el usuario en Origin (Accounting)
            ->setParameter('userId', $user->getId());
    }
}
