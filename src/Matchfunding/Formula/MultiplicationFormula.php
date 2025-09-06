<?php

namespace App\Matchfunding\Formula;

use App\Money\Money;
use App\Money\MoneyInterface;
use App\Money\MoneyService;

class MultiplicationFormula implements FormulaInterface
{
    public static function getName(): string
    {
        return 'multiplication';
    }

    public static function getAsExpression(): string
    {
        return 'min(factor * money, limit)';
    }

    public function match(float $factor, MoneyInterface $money, MoneyInterface $limit): Money
    {
        $delta = \Brick\Money\Money::min(
            MoneyService::toBrick($limit),
            MoneyService::toBrick($money)->multipliedBy($factor)
        );

        return MoneyService::toMoney($delta);
    }
}
