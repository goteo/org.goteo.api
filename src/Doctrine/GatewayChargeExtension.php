<?php

namespace App\Doctrine;

use App\Entity\Gateway\Charge;
use App\Security\Trait\UserTrait;
use Doctrine\ORM\QueryBuilder;

final class GatewayChargeExtension extends AbstractQueryResourceExtensionInterface
{
    use UserTrait;

    private const ARE_PUBLIC = true;

    protected function supports(string $resourceClass): bool
    {
        return $resourceClass === Charge::class;
    }

    protected function applyFilters(QueryBuilder $queryBuilder, string $rootAlias): void
    {
        if (self::ARE_PUBLIC) {
            return;
        }

        $user = $this->getAuthenticatedUser();

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
