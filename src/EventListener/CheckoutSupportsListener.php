<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Project\Support;
use App\Gateway\CheckoutStatus;
use App\Service\Project\SupportService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::preUpdate,
    method: 'preUpdate',
    entity: Checkout::class
)]
class CheckoutSupportsListener
{
    use RecomputingListenerTrait;

    public function __construct(
        private SupportService $supportService,
    ) {}

    public function preUpdate(Checkout $checkout, PreUpdateEventArgs $event): void
    {
        if (!$event->hasChangedField('status')) {
            return;
        }

        if ($event->getNewValue('status') !== CheckoutStatus::Charged) {
            return;
        }

        $supports = $this->prepareSupports($checkout);

        foreach ($supports as $support) {
            $this->computeChangeSet($event->getObjectManager(), $support);
        }
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
            $transactionsByProject[$projectId] = [
                ...$transactionsByProject[$projectId] ?? [],
                ...$charge->getTransactions(),
            ];
        }

        $supports = [];
        foreach ($transactionsByProject as $projectId => $transactions) {
            $support = $this->supportService->getSupport($projects[$projectId], $origin);
            $support = $this->supportService->withTransactions($support, $transactions);
            $supports[] = $support;
        }

        return $supports;
    }
}
