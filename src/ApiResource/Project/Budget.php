<?php

namespace App\ApiResource\Project;

use ApiPlatform\Metadata as API;

class Budget
{
    /**
     * A summary of the minimum budget. As described by items with specified minimum money.
     */
    #[API\ApiProperty(writable: false)]
    public BudgetSummary $minimum;

    /**
     * A summary of the optimum, minimum included, budget. As described by items with specified optimum money plus minimum money.
     */
    #[API\ApiProperty(writable: false)]
    public BudgetSummary $optimum;
}
