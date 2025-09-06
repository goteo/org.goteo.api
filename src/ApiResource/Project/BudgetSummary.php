<?php

namespace App\ApiResource\Project;

use App\ApiResource\ApiMoney;

class BudgetSummary
{
    /**
     * The total money by the included items.
     */
    public ApiMoney $money;

    /**
     * The total money of type 'task'.
     */
    public ApiMoney $task;

    /**
     * The total money of type 'material'.
     */
    public ApiMoney $material;

    /**
     * The total money of type 'infrastructure'.
     */
    public ApiMoney $infra;
}
