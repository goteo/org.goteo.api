<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::preUpdate,
    method: 'preUpdate',
    entity: Checkout::class
)]
#[AsEntityListener(
    event: Events::postUpdate,
    method: 'postUpdate',
    entity: Checkout::class
)]
class GatewayCheckoutSupportsListener
{
    /** @var array<int, Support> */
    private array $supports;

    public function preUpdate(Checkout $checkout, PreUpdateEventArgs $args): void
    {
        if (!$args->hasChangedField('status')) {
            return;
        }

        if ($checkout->isCharged()) {
            $this->supports = $this->prepareSupports($checkout, $args->getObjectManager());
        }
    }

    public function postUpdate(Checkout $checkout, PostUpdateEventArgs $args): void
    {
        if (empty($this->supports)) {
            return;
        }

        foreach ($this->supports as $key => $support) {
            $args->getObjectManager()->persist($support);

            unset($this->supports[$key]);
        }

        $args->getObjectManager()->flush();
    }

    /**
     * Create ProjectSupport for the given data.
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

    /**
     * @return Support[]
     */
    public function prepareSupports(Checkout $checkout): array
    {
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

        $supports = [];
        foreach ($chargesInProjectMap as $chargesInProject) {
            $project = $chargesInProject[0]->getTarget()->getProject();
            $supports[] = $this->createSupport($project, $owner, $chargesInProject);
        }

        return $supports;
    }
}
