<?php

namespace App\EventListener;

use App\Entity\Accounting\Transaction;
use App\Entity\EmbeddableMoney;
use App\Money\MoneyService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    entity: Transaction::class,
    event: Events::postPersist
)]
class AccountingBalanceListener
{
    public function __construct(
        private MoneyService $moneyService,
    ) {}

    public function postPersist(Transaction $transaction, PostPersistEventArgs $event): void
    {
        $money = $transaction->getMoney();

        $origin = $transaction->getOrigin();
        $origin->setBalance(EmbeddableMoney::of(
            $this->moneyService->substract($money, $origin->getBalance())
        ));

        $target = $transaction->getTarget();
        $target->setBalance(EmbeddableMoney::of(
            $this->moneyService->add($money, $target->getBalance())
        ));

        $event->getObjectManager()->persist($origin);
        $event->getObjectManager()->persist($target);
        $event->getObjectManager()->flush();
    }
}
