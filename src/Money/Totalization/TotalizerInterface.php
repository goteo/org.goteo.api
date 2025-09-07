<?php

namespace App\Money\Totalization;

interface TotalizerInterface
{
    /**
     * @return string the resource that can be totalized by this Totalizer
     */
    public static function getSupportedResource(): string;

    /**
     * @param iterable $items The resource Collection
     */
    public function totalize(iterable $items): TotalizedMoney;
}
