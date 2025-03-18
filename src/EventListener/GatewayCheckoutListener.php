<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
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
    /**
     * Summary of createSupport
     * @param \App\Entity\Project\Project $project
     * @param \App\Entity\User\User $owner
     * @param Charge[] $charges
     * @return Support
     */
    private function createSupport(Project $project, User $owner, array $charges): Support
    {
        $projectSupport = new Support();
        $projectSupport->setProject($project);
        $projectSupport->setOwner($owner);

        foreach($charges as $charge){
            $projectSupport->addCharge($charge);
        }

        return $projectSupport;
    }

    public function postPersist(Checkout $checkout, PostPersistEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();

        $charges = $checkout->getCharges()->toArray();
        $owner = $checkout->getOrigin()->getUser();

        // Group charges for projects
        $chargesByProject = [];
        foreach($charges as $charge){
            $project = $charge->getTarget()->getProject();
            $chargesByProject[$project->getId()][] = $charge;
        }

        // Create Project Supports for each project
        foreach($chargesByProject as $chargeByProject){
            $project = $chargeByProject[0]->getTarget()->getProject();
            if($project === null){
                continue;
            }
            
            $support = $this->createSupport($project, $owner, $chargeByProject);
            $objectManager->persist($support);
        }

        $objectManager->flush();
    }
}
