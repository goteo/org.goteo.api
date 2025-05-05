<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Gateway\ChargeStatus;
use App\Service\Matchfunding\MatchfundingService;
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
    /**
     * @var Charge|null
     */
    private ?Charge $charge = null;

    public function __construct(
        private MatchfundingService $matchfunding,
        private EntityManagerInterface $entityManager,
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

        $transactions = $this->matchfunding->match($charge);

        if (\count($transactions) > 0) {
            foreach ($transactions as $transaction) {
                $charge->addTransaction($transaction);
            }

            $this->charge = $charge;
        }
    }

    public function postUpdate()
    {
        if ($this->charge === null) {
            return;
        }

        $this->entityManager->persist($this->charge);
        $this->entityManager->flush();

        $this->charge = null;
    }
}
