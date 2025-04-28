<?php

namespace App\ApiResource\Matchfunding;

use App\ApiResource\Money;
use App\Entity\Matchfunding\MatchAgainst;
use Symfony\Component\Validator\Constraints as Assert;

class MatchStrategyApiResource
{
    /**
     * The MatchFormula used to calculate matched funds.
     */
    #[Assert\NotBlank()]
    public FormulaApiResource $formula;

    /**
     * The assigned maximum amount of funding that will be given by the MatchFormula per operation.
     */
    #[Assert\NotBlank()]
    #[Assert\Valid()]
    public Money $limit;

    /**
     * The `x` factor used to calculate the resulting match of funds with the MatchFormula.
     */
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    public float $factor;

    /**
     * The money to be matched by the formula is
     * - `charge` the money in the Charge item
     * - `budget_min` the minimum in the Project's budget
     * - `budget_opt` the optimum in the Project's budget.
     */
    #[Assert\NotBlank()]
    public MatchAgainst $match = MatchAgainst::Charge;
}
