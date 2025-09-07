<?php

namespace App\Money;

use App\Money\Conversion\Conversion;

class Money implements MoneyInterface
{
    public function __construct(
        private int $amount,
        private string $currency,
        private ?Conversion $conversion = null,
    ) {}

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getConversion(): ?Conversion
    {
        return $this->conversion;
    }
}
