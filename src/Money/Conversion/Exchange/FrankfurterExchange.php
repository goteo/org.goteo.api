<?php

namespace App\Money\Conversion\Exchange;

use App\Money\Conversion\ExchangeInterface;
use App\Money\Money;
use App\Money\MoneyInterface;
use App\Money\MoneyService;
use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider;
use Brick\Money\ExchangeRateProvider\BaseCurrencyProvider;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FrankfurterExchange implements ExchangeInterface
{
    private const NAME = 'frankfurter';
    private const WEIGHT = 200;

    private const ENDPOINT = 'https://api.frankfurter.dev/v1/latest';
    private const ISO_4217 = 'EUR';

    private ExchangeRateProvider $provider;
    private CurrencyConverter $converter;
    private \DateTimeImmutable $date;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getWeight(): int
    {
        return self::WEIGHT;
    }

    public function __construct(
        HttpClientInterface $httpClient,
    ) {
        $response = $httpClient->request('GET', self::ENDPOINT);
        $data = $response->toArray();

        $provider = new ConfigurableProvider();
        foreach ($data['rates'] as $currency => $rate) {
            $provider->setExchangeRate(self::ISO_4217, $currency, $rate);
        }

        $this->date = new \DateTimeImmutable($data['date']);
        $this->provider = new BaseCurrencyProvider($provider, self::ISO_4217);
        $this->converter = new CurrencyConverter($this->provider);
    }

    public function convert(MoneyInterface $money, string $toCurrency): MoneyInterface
    {
        $converted = $this->converter->convert(
            MoneyService::toBrick($money),
            $toCurrency,
            null,
            RoundingMode::HALF_EVEN
        );

        return new Money(
            $converted->getMinorAmount()->toInt(),
            $converted->getCurrency()->getCurrencyCode()
        );
    }

    public function getConversionRate(string $fromCurrency, string $toCurrency): float
    {
        return $this->provider->getExchangeRate($fromCurrency, $toCurrency)->toFloat();
    }

    public function getConversionDate(string $fromCurrency, string $toCurrency): \DateTimeInterface
    {
        return $this->date;
    }
}
