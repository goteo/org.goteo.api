<?php

namespace App\ApiResource\Project;

use App\ApiResource\MoneyWithConversion;

class BudgetSummary
{
    /**
     * The total money by the included items.
     */
    public MoneyWithConversion $money;

    /**
     * The total money of type 'task'.
     */
    public MoneyWithConversion $task;

    /**
     * The total money of type 'material'.
     */
    public MoneyWithConversion $material;

    /**
     * The total money of type 'infrastructure'.
     */
    public MoneyWithConversion $infra;
}
