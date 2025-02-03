<?php

namespace App\ApiResource\Project;

use ApiPlatform\Metadata as API;

class Budget
{
    /**
     * A summary of the minimum budget, as described by items with specified minimum money.
     */
    #[API\ApiProperty(writable: false)]
    public BudgetSummary $minimum;

    /**
     * A summary of the optimum budget, as described by items with specified optimum money.
     */
    #[API\ApiProperty(writable: false)]
    public BudgetSummary $optimum;
}
