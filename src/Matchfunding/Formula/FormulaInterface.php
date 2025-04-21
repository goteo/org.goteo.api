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
     * Process an input Money and obtain a delta Money, which is the Money to be given by the Matcher.
     *
     * @param Money $money The Money input that is to be matched
     *
     * @return Money A Money delta
     */
    public function match(Money $money): Money;
}
