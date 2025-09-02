<?php

namespace App\EventListener;

use App\Entity\Accounting\Accounting;
use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
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
     */
    private function createSupport(Project $project, Accounting $origin, array $transactions): Support
    {
        $projectSupport = new Support();
        $projectSupport->setProject($project);
        $projectSupport->setOrigin($origin);
        $projectSupport->setAnonymous(false);

        foreach ($transactions as $transaction) {
            $projectSupport->addTransaction($transaction);
        }

        return $projectSupport;
    }

    /**
     * @return Support[]
     */
    public function prepareSupports(Checkout $checkout): array
    {
        /** @var Charge[] */
        $charges = $checkout->getCharges()->toArray();
        $origin = $checkout->getOrigin();

        $projects = [];
        $transactionsByProject = [];
        foreach ($charges as $charge) {
            $project = $charge->getTarget()->getProject();

            if (!$project) {
                continue;
            }

            $projectId = $project->getId();

            $projects[$projectId] = $project;
            $transactionsByProject[$projectId] = [...$transactionsByProject[$projectId], ...$charge->getTransactions()];
        }

        $supports = [];
        foreach ($transactionsByProject as $projectId => $transactions) {
            $supports[] = $this->createSupport($projects[$projectId], $origin, $transactions);
        }

        return $supports;
    }
}
