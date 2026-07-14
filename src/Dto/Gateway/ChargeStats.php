<?php

namespace App\Dto\Gateway;

/**
 * Aggregate statistics for a filtered GatewayCharge collection.
 */
class ChargeStats
{
    public function __construct(private int $distinctProjectCount) {}

    /**
     * The number of distinct Projects targeted by the charges matching the filter set.
     */
    public function getDistinctProjectCount(): int
    {
        return $this->distinctProjectCount;
    }
}
