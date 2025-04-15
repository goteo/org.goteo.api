<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Gateway\AbstractGateway;
use App\Gateway\CheckoutStatus;
use App\Gateway\RefundStrategy;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::preUpdate,
    method: 'preUpdate',
    entity: Charge::class
)]
class ChargeRefundListener
{
    public function __construct(
        private AbstractGateway $gateway,
    ) {}

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
            if ($charge->getCheckout()->getRefundStrategy() != RefundStrategy::ToWallet) {
                $this->gateway->processRefund($charge);
            }
        }
    }
}
