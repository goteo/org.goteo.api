<?php

namespace App\Money\Totalization;

use App\Money\MoneyInterface;

class TotalizedMoney implements MoneyInterface
{
    public function __construct(
        private int $amount,
        private string $currency,
        private int $length,
    ) {}

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return int the total number of items that were totalized to produce the Money
     */
    public function getLength(): int
    {
        return $this->length;
    }
}
