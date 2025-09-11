<?php

namespace App\EventListener;

use App\Entity\Accounting\Accounting;
use App\Money\MoneyService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(
    entity: Accounting::class,
    method: 'prePersist',
    event: Events::prePersist
)]
class AccountingDefaultsListener
{
    public function __construct(
        private MoneyService $moneyService,
    ) {}

    public function prePersist(Accounting $accounting): void
    {
        if ($accounting->getCurrency() === null) {
            $accounting->setCurrency($this->moneyService->getDefaultCurrency());
        }
    }
}
