<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Entity\User\User;
use App\Gateway\ChargeStatus;
use App\Gateway\Wallet\WalletService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::postPersist,
    method: 'postPersist',
    entity: Charge::class
)]
final class ChargeWalletListener
{
    public function __construct(
        private WalletService $wallet,
    ) {}

    /**
     * Generates an income statement for User-targeting Charges.
     */
    public function postPersist(Charge $charge, PostPersistEventArgs $event)
    {
        if (
            $charge->getStatus() !== ChargeStatus::InCharge
            || !$charge->getTarget()->getOwner() instanceof User
        ) {
            return;
        }

        $income = $this->wallet->save($charge->getTransactions()->last());

        if ($income->getId() !== null) {
            return;
        }

        $event->getObjectManager()->persist($income);
        $event->getObjectManager()->flush();
    }
}
