<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Gateway\ChargeStatus;
use App\Gateway\GatewayLocator;
use App\Gateway\RefundStrategy;
use App\Service\Gateway\ChargeService;
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
        private ChargeService $transactionService,
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

        if (
            $args->getOldValue(self::FIELD_STATUS) === ChargeStatus::Charged->value
            && $args->getNewValue(self::FIELD_STATUS) === ChargeStatus::ToRefund->value
        ) {
            $refundStrategy = $charge->getCheckout()->getRefundStrategy();
            match ($refundStrategy) {
                RefundStrategy::ToGateway => $this->processGatewayRefund($charge),
                default => throw new \LogicException(sprintf(
                    'Refund strategy "%s" is not implemented.',
                    $refundStrategy->name
                )),
            };

            $this->transactionService->addRefundTransaction($charge);
        }
    }
}
