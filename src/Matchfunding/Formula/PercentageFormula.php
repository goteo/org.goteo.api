<?php

namespace App\Matchfunding\Formula;

use App\Entity\Money;
use App\Money\MoneyService;

class PercentageFormula implements FormulaInterface
{
    public static function getName(): string
    {
        return 'percentage';
    }

    public static function getAsExpression(): string
    {
        return 'min(factor * money / 100, limit)';
    }

    public function match(float $factor, Money $money, Money $limit): Money
    {
        $delta = \Brick\Money\Money::min(
            MoneyService::toBrick($limit),
            MoneyService::toBrick($money)
                ->multipliedBy($factor)
                ->dividedBy(100)
        );

        return MoneyService::toMoney($delta);
    }
}
