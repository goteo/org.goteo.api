<?php

namespace App\Service\Gateway;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use App\Entity\Gateway\Charge;
use App\Gateway\ChargeStatus;

class ChargeService
{
    private function addTransaction(
        Charge $charge,
        Accounting $origin,
        Accounting $target,
        ChargeStatus $status,
    ): Charge {
        $transaction = new Transaction();
        $transaction->setMoney($charge->getMoney());
        $transaction->setOrigin($origin);
        $transaction->setTarget($target);

        $charge->addTransaction($transaction);
        $charge->setStatus($status);

        return $charge;
    }

    private function addTransactionByStatus(Charge $charge, ChargeStatus $status): Charge
    {
        $checkoutOrigin = $charge->getCheckout()->getOrigin();
        $chargeTarget = $charge->getTarget();

        [$origin, $target] = match ($status) {
            ChargeStatus::InCharge => [$checkoutOrigin, $chargeTarget],
            ChargeStatus::Refunded => [$chargeTarget, $checkoutOrigin],
            default => throw new \InvalidArgumentException('Invalid charge status'),
        };

        return $this->addTransaction($charge, $origin, $target, $status);
    }

    public function addRefundTransaction(Charge $charge): Charge
    {
        return $this->addTransactionByStatus($charge, ChargeStatus::Refunded);
    }

    public function addChargeTransaction(Charge $charge): Charge
    {
        return $this->addTransactionByStatus($charge, ChargeStatus::InCharge);
    }
}
