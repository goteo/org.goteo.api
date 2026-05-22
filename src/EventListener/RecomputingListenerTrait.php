<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;

trait RecomputingListenerTrait
{
    /**
     * Appropriately updates the Doctrine's Unit of Work and marks entities for persistence.
     */
    private function computeChangeSet(EntityManagerInterface $em, object $entity): void
    {
        $uow = $em->getUnitOfWork();

        $meta = $em->getClassMetadata($entity::class);

        if ($entity->getId()) {
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        } else {
            $em->persist($entity);
            $uow->computeChangeSet($meta, $entity);
        }
    }
}
