<?php

namespace App\Matchfunding\Formula;

use App\Entity\Money;
use App\Library\Economy\MoneyService;
use Brick\Math\BigNumber;

class PercentageFormula implements FormulaInterface
{
    public static function getName(): string
    {
        return 'percentage';
    }

    public static function getAsExpression(): string
    {
        return 'min(limit, factor / 100 * money)';
    }

    public function match(BigNumber $factor, Money $money, Money $limit): Money
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
