<?php

namespace App\ApiResource\Project;

use App\Entity\Money;

class BudgetSummary
{
    /**
     * The total money by the included items.
     */
    public Money $money;

    /**
     * The total money of type 'task'.
     */
    public Money $task;

    /**
     * The total money of type 'material'.
     */
    public Money $material;

    /**
     * The total money of type 'infrastructure'.
     */
    public Money $infra;
}
