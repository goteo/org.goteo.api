<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Gateway\ChargeStatus;
use App\Gateway\GatewayLocator;
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
    private const FIELD_STATUS = 'status';

    public function __construct(
        private GatewayLocator $gatewayLocator,
    ) {}

    private function processGatewayRefund(Charge $charge): void
    {
        $gateway = $this->gatewayLocator->getForCheckout($charge->getCheckout());
        $gateway->processRefund($charge);
    }

    public function preUpdate(Charge $charge, PreUpdateEventArgs $args): void
    {
        if (!$args->hasChangedField(self::FIELD_STATUS)) {
            return;
        }

        $oldStatus = $args->getOldValue(self::FIELD_STATUS);
        $newStatus = $args->getNewValue(self::FIELD_STATUS);

        $charged = ChargeStatus::Charged->value;
        $toRefund = ChargeStatus::ToRefund->value;
        if ($oldStatus === $charged && $newStatus === $toRefund) {
            $refundStrategy = $charge->getCheckout()->getRefundStrategy();
            match ($refundStrategy) {
                RefundStrategy::ToGateway => $this->processGatewayRefund($charge),
                default => throw new \LogicException(sprintf(
                    'Refund strategy "%s" is not implemented.',
                    $refundStrategy->name
                )),
            };

            $charge->setStatus(ChargeStatus::Refunded);
        }
    }
}
