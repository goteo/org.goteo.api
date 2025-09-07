<?php

use App\Entity\Accounting\Transaction;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(
    entity: Transaction::class,
    event: Events::prePersist
)]
class TransactionCurrencyListener
{
    public function prePersist(Transaction $transaction): void
    {
        $money = $transaction->getMoney();
        $target = $transaction->getTarget();

        if ($money->getCurrency() !== $target->getCurrency()) {
            throw new \LogicException(\sprintf(
                "The Accounting with ID %s can only receive Transactions in %s currency. Please do a conversion operation before targeting an Accounting in a different currency.",
                $target->getId(),
                $target->getCurrency()
            ));
        }
    }
}
