<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
use App\Gateway\ChargeStatus;
use App\Service\Matchfunding\MatchfundingService;
use App\Service\Project\SupportService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::preUpdate,
    method: 'preUpdate',
    entity: Charge::class
)]
#[AsEntityListener(
    event: Events::postUpdate,
    method: 'postUpdate',
    entity: Charge::class
)]
final class MatchfundingListener
{
    private ?Charge $charge = null;
    private ?Support $support = null;

    public function __construct(
        private MatchfundingService $matchfunding,
        private EntityManagerInterface $entityManager,
        private SupportService $supportService,
    ) {}

    public function preUpdate(Charge $charge, PreUpdateEventArgs $event)
    {
        $this->charge = null;

        if (count($event->getEntityChangeSet()) === 0) {
            return;
        }

        if ($charge->getStatus() !== ChargeStatus::Charged) {
            return;
        }

        $target = $charge->getTarget()->getOwner();
        if (!$target instanceof Project) {
            return;
        }

        $transactions = $this->matchfunding->match($charge);

        $origin = $charge->getCheckout()->getOrigin();
        $support = $target->getMatchSupport($origin)
        ?? $this->supportService->createSupport($target, $origin);

        if (\count($transactions) > 0) {
            foreach ($transactions as $transaction) {
                $charge->addTransaction($transaction);
                $support->addTransaction($transaction);
            }

            $this->charge = $charge;
            $this->support = $support;
        }
    }

    public function postUpdate()
    {
        if ($this->charge === null) {
            return;
        }

        $this->entityManager->persist($this->charge);

        if ($this->support !== null) {
            $this->entityManager->persist($this->support);
        }

        $this->entityManager->flush();

        $this->charge = null;
        $this->support = null;
    }
}
