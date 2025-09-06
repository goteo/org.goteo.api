<?php

namespace App\Money\Totalization;

interface TotalizerInterface
{
    /**
     * @return string the resource that can be totalized by this Totalizer
     */
    public static function getSupportedResource(): string;

    /**
     * @param array $filters an array of filters to be applied to the resource Collection
     */
    public function totalize(array $filters): TotalizedMoney;
}
