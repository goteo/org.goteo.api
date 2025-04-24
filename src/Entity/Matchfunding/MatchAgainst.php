<?php

namespace App\Entity\Matchfunding;

/**
 * Defines against what amount of money a Matchfunding shall be.
 */
enum MatchAgainst: string
{
    /**
     * The money to be matched will be extracted from a Charge item.
     */
    case Charge = 'charge';

    /**
     * The money to be matched will be from the minimum amount in a Project's budget.
     */
    case BudgetMin = 'budget_min';

    /**
     * The money to be matched will be from the optimum amount in a Project's budget.
     */
    case BudgetOpt = 'budget_opt';
}
