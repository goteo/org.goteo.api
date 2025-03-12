<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Gateway\Checkout;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class GatewayCheckoutExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {}

    /**
     *  Method that applies the filter.
     */
    public function addFilter(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass !== Checkout::class) {
            return; // Just apply the filter to GatewayCharge
        }

        $user = $this->security->getUser();

        if (!$user instanceof \App\Entity\User\User) {
            return; // If the user is not an instance of User, we do not apply the filter
        }

        // If the user has ROLE_ADMIN, we dont filter the results
        if ($user->hasRoles(['ROLE_ADMIN'])) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Filter by the owner user
        $queryBuilder
            ->leftJoin("$rootAlias.origin", 'co') // Relationship with Accounting
            ->leftJoin('co.user', 'u') // Relationship with User
            ->andWhere('u.id = :userId') // Filter by the user in the Origin entity (accounting)
            ->setParameter('userId', $user->getId());
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
