<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Gateway\ChargeStatus;
use App\Gateway\RefundStrategy;
use App\Gateway\Stripe\StripeGateway;
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
        private StripeGateway $gateway,
    ) {}

    public function preUpdate(Charge $charge, PreUpdateEventArgs $args): void
    {
        $status = 'status';
        if (!$args->hasChangedField($status)) {
            return;
        }

        $oldStatus = $args->getOldValue($status);
        $newStatus = $args->getNewValue($status);

        $charged = ChargeStatus::Charged->value;
        $toRefund = ChargeStatus::ToRefund->value;
        if ($oldStatus === $charged && $newStatus === $toRefund) {
            if ($charge->getCheckout()->getRefundStrategy() != RefundStrategy::ToWallet) {
                $this->gateway->processRefund($charge);
                $charge->setStatus(ChargeStatus::Refunded);
            }
        }
    }
}
