<?php

namespace App\EventListener;

use App\Entity\Gateway\Checkout;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Checkout::class)]
class GatewayCheckoutListener
{
    private function createSupport(Project $project, User $owner): Support
    {
        $projectSupport = new Support();
        $projectSupport->setProject($project);
        $projectSupport->setOwner($owner);

        return $projectSupport;
    }

    public function postPersist(Checkout $checkout, PostPersistEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();

        $charges = $checkout->getCharges();

        $uniqueProjects = new \SplObjectStorage();

        foreach ($charges as $charge) {
            $project = $charge->getTarget()->getProject();

            if ($project !== null && !$uniqueProjects->contains($project)) {
                $uniqueProjects->attach($project);

                $support = $this->createSupport($project, $checkout->getOrigin()->getUser());

                $objectManager->persist($support);
            }
        }

        $objectManager->flush();
    }
}
