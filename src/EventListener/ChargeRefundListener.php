<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Gateway\AbstractGateway;
use App\Gateway\CheckoutStatus;
use App\Gateway\RefundStrategy;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

class ChargeRefundListener
{
    public function __construct(
        private AbstractGateway $gateway,
    ) {}

    #[ORM\PreUpdate]
    public function preUpdate(Charge $charge, PreUpdateEventArgs $args): void
    {
        $status = 'status';
        if (!$args->hasChangedField($status)) {
            return;
        }

        $oldStatus = $args->getOldValue($status);
        $newStatus = $args->getNewValue($status);

        $charged = CheckoutStatus::Charged->value;
        $toRefund = CheckoutStatus::ToRefund->value;
        if ($oldStatus === $charged && $newStatus === $toRefund) {
            if ($charge->getRefundStrategy() != RefundStrategy::ToWallet) {
                $this->gateway->processRefund($charge);
            }
        }
    }
}
