<?php

namespace App\Service;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use App\Entity\Gateway\Charge;
use App\Gateway\ChargeStatus;

class TransactionService
{
    public function addRefundTransaction(Charge $charge): Charge
    {
        $transaction = new Transaction();
        $transaction->setMoney($charge->getMoney());
        $transaction->setOrigin($charge->getTarget());
        $transaction->setTarget($charge->getCheckout()->getOrigin());

        $charge->addTransaction($transaction);
        $charge->setStatus(ChargeStatus::Refunded);

        return $charge;
    }

    public function addChargeTransaction(Charge $charge, Accounting $origin): Charge
    {
        $transaction = new Transaction();
        $transaction->setMoney($charge->getMoney());
        $transaction->setOrigin($origin);
        $transaction->setTarget($charge->getTarget());

        $charge->addTransaction($transaction);
        $charge->setStatus(ChargeStatus::Charged);

        return $charge;
    }
}
