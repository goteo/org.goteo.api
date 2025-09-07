<?php

namespace App\Money;

use App\Money\Conversion\Conversion;

interface MoneyInterface
{
    /**
     * @return int The monetary amount value. As expressed in the minor unit of the currency.
     */
    public function getAmount(): int;

    /**
     * @return string 3-letter ISO 4217 currency code
     */
    public function getCurrency(): string;

    /**
     * @return Conversion|null If this Money is the result of a currency conversion operation. Else null.
     */
    public function getConversion(): ?Conversion;
}
