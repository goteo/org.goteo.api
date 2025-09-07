<?php

namespace App\Money\Conversion\Exchange;

use App\Money\Conversion\Conversion;
use App\Money\Conversion\ExchangeInterface;
use App\Money\Money;
use App\Money\MoneyInterface;
use App\Money\MoneyService;
use Brick\Math\RoundingMode;
use Brick\Money\Context;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider;

abstract class AbstractExchange implements ExchangeInterface
{
    protected string $date;

    protected CurrencyConverter $converter;
    protected ExchangeRateProvider $provider;

    public function convert(
        MoneyInterface $from,
        string $toCurrency,
        ?Context $context = null,
        RoundingMode $roundingMode = RoundingMode::UP,
    ): MoneyInterface {
        $converted = $this->converter->convert(
            MoneyService::toBrick($from),
            $toCurrency,
            $context,
            $roundingMode
        );

        return new Money(
            $converted->getMinorAmount()->toInt(),
            $converted->getCurrency()->getCurrencyCode(),
            new Conversion(
                $from,
                MoneyService::toMoney($converted),
                rate: $this->getConversionRate($from->getCurrency(), $toCurrency),
                date: $this->getConversionDate($from->getCurrency(), $toCurrency),
                provider: $this->getName(),
                roundingMode: $roundingMode
            )
        );
    }

    public function getConversionRate(string $fromCurrency, string $toCurrency): float
    {
        return $this->provider->getExchangeRate($fromCurrency, $toCurrency)->toFloat();
    }

    public function getConversionDate(string $fromCurrency, string $toCurrency): string
    {
        return $this->date;
    }
}
