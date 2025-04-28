<?php

namespace App\Matchfunding\Formula;

use App\Entity\Money;

interface FormulaInterface
{
    /**
     * A unique name for the formula.
     */
    public static function getName(): string;

    /**
     * The underlying math expressed as a common-notation formula.
     */
    public static function getAsExpression(): string;

    /**
     * Process an input Money and obtain a delta Money, which is the Money to be given by the Matcher.
     *
     * @param float $factor A matching factor for the formula to use
     * @param Money     $money  The input Money that is to be matched
     * @param Money     $limit  An upper bound for the matching formula, the delta won't be higher than this
     *
     * @return Money The output Money delta
     */
    public function match(float $factor, Money $money, Money $limit): Money;
}
