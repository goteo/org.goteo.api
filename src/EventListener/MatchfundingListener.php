<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
use App\Gateway\ChargeStatus;
use App\Service\Matchfunding\MatchfundingService;
use App\Service\Project\SupportService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /** @var Collection<int, Support> */
    private Collection $supports;

    public function __construct(
        private MatchfundingService $matchfunding,
        private EntityManagerInterface $entityManager,
        private SupportService $supportService,
    ) {}

    public function preUpdate(Charge $charge, PreUpdateEventArgs $event)
    {
        $this->charge = null;
        $this->supports = new ArrayCollection();

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

        if (\count($transactions) > 0) {
            foreach ($transactions as $transaction) {
                $charge->addTransaction($transaction);

                $origin = $transaction->getOrigin();
                // You can only generate an support by origin in Matchfunding
                $matchSupport = $target->getSupportsByOrigin($origin)->first();
                if (!$matchSupport instanceof Support) {
                    $matchSupport = $this->supportService->createSupport($target, $origin);
                }

                if ($this->supports->contains($matchSupport)) {
                    $matchSupport = $this->supports->get($this->supports->indexOf($matchSupport));
                } else {
                    $this->supports->add($matchSupport);
                }
                $matchSupport->addTransaction($transaction);
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

        foreach ($this->supports as $support) {
            $this->entityManager->persist($support);
        }

        $this->entityManager->flush();

        $this->charge = null;
        $this->supports = new ArrayCollection();
    }
}
