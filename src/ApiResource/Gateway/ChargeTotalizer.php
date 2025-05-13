<?php

namespace App\ApiResource\Gateway;

use ApiPlatform\Metadata as API;
use App\Entity\Money;
use App\State\Gateway\ChargeTotalizerStateProvider;

#[API\ApiResource(shortName: 'GatewayChargeTotalizer')]
#[API\Get(
    provider: ChargeTotalizerStateProvider::class,
)]
class ChargeTotalizer
{
    /**
     * Resulting total balance.
     */
    public Money $balance;
}
