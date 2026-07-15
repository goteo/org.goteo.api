<?php

namespace App\Dto\Gateway;

/**
 * Aggregate totals for a filtered GatewayCharge collection.
 */
class ChargesTotalsDto
{
    public function __construct(private int $projects) {}

    /**
     * The number of distinct Projects targeted by the charges matching the filter set.
     */
    public function getProjects(): int
    {
        return $this->projects;
    }
}
