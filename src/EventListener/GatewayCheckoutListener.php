<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Checkout::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Checkout::class)]
class GatewayCheckoutListener
{
    /**
     * Summary of createSupport.
     *
     * @param Charge[] $charges
     */
    private function createSupport(Project $project, User $owner, array $charges): Support
    {
        $projectSupport = new Support();
        $projectSupport->setProject($project);
        $projectSupport->setOwner($owner);
        $projectSupport->setAnonymous(false);

        foreach ($charges as $charge) {
            $projectSupport->addCharge($charge);
        }

        return $projectSupport;
    }

    public function persistSupports(Checkout $checkout, ObjectManager $objectManager): void
    {
        if (!$checkout->isCharged()) {
            return;
        }

        $charges = $checkout->getCharges()->toArray();
        $owner = $checkout->getOrigin()->getUser();

        // Group charges for projects
        $chargesInProjectMap = [];
        foreach ($charges as $charge) {
            $project = $charge->getTarget()->getProject();
            if ($project === null) {
                continue;
            }

            $chargesInProjectMap[$project->getId()][] = $charge;
        }

        // Create Project Support for each project
        foreach ($chargesInProjectMap as $chargesInProject) {
            $project = $chargesInProject[0]->getTarget()->getProject();
            $support = $this->createSupport($project, $owner, $chargesInProject);
            $objectManager->persist($support);
        }

        $objectManager->flush();
    }

    public function postPersist(Checkout $checkout, PostPersistEventArgs $event): void
    {
        $this->persistSupports($checkout, $event->getObjectManager());
    }

    public function preUpdate(Checkout $checkout, PreUpdateEventArgs $event): void
    {
        if (!$event->hasChangedField('status')) {
            return;
        }

        $this->persistSupports($checkout, $event->getObjectManager());
    }
}
