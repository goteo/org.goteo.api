<?php

namespace App\Doctrine;

use App\Entity\Gateway\Checkout;
use App\Security\Trait\UserTrait;
use Doctrine\ORM\QueryBuilder;

final class GatewayCheckoutExtension extends AbstractQueryResourceExtensionInterface
{
    use UserTrait;

    protected function supports(string $resourceClass): bool
    {
        return Checkout::class === $resourceClass;
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
            ->leftJoin("$rootAlias.origin", 'co')
            ->leftJoin('co.user', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId());
    }
}
