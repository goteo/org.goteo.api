<?php

namespace App\Money\Conversion\Exchange;

use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider\BaseCurrencyProvider;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FrankfurterExchange extends AbstractExchange
{
    private const NAME = 'frankfurter';
    private const WEIGHT = 200;

    private const ENDPOINT = 'https://api.frankfurter.dev/v1/latest';
    private const ISO_4217 = 'EUR';

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

        $this->date = $data['date'];
        $this->provider = new BaseCurrencyProvider($provider, self::ISO_4217);
        $this->converter = new CurrencyConverter($this->provider);
    }
}
