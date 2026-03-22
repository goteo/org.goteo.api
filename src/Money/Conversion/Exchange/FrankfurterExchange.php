<?php

namespace App\Money\Conversion\Exchange;

use App\Money\MoneyInterface;
use Brick\Math\RoundingMode;
use Brick\Money\Context;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider\BaseCurrencyProvider;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FrankfurterExchange extends AbstractExchange
{
    private const NAME = 'frankfurter';
    private const WEIGHT = 200;

    private const ISO_4217 = 'EUR';

    private const ENDPOINT = 'https://api.frankfurter.dev/v1/latest';

    private const CACHE_TTL = 86400;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getWeight(): int
    {
        return self::WEIGHT;
    }

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
    ) {}

    public function convert(MoneyInterface $from, string $toCurrency, ?Context $context = null, RoundingMode $roundingMode = RoundingMode::UP): MoneyInterface
    {
        $data = $this->cache->get(
            self::NAME,
            function (CacheItemInterface $item) {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->getDataLatest();
            }
        );

        $provider = new ConfigurableProvider();
        foreach ($data['rates'] as $currency => $rate) {
            $provider->setExchangeRate(self::ISO_4217, $currency, $rate);
        }

        $this->date = $data['date'];
        $this->provider = new BaseCurrencyProvider($provider, self::ISO_4217);
        $this->converter = new CurrencyConverter($this->provider);

        return parent::convert($from, $toCurrency, $context, $roundingMode);
    }

    private function getDataLatest(): array
    {
        $response = $this->httpClient->request('GET', self::ENDPOINT);

        return $response->toArray();
    }
}
