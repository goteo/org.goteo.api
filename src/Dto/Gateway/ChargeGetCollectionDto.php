<?php

namespace App\Dto\Gateway;

use ApiPlatform\Metadata\ApiResource;
use App\ApiResource\Money;

#[ApiResource]
class ChargeGetCollectionDto
{
    public Money $totalContributions;
    public Money $totalTips;

    /** @var iterable<ChargeApiResource> */
    public iterable $charges;

    public function __construct(Money $totalContributions, Money $totalTips, iterable $charges)
    {
        $this->totalContributions = $totalContributions;
        $this->totalTips = $totalTips;
        $this->charges = $charges;
    }
}
