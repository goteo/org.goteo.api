<?php

namespace App\Money;

use App\Money\Currency\ExchangeLocator;
use Brick\Money\Money as BrickMoney;

class MoneyService
{
    public function __construct(
        private ExchangeLocator $exchangeLocator,
    ) {}

    public static function toMoney(BrickMoney $brick): Money
    {
        return new Money(
            $brick->getMinorAmount()->toInt(),
            $brick->getCurrency()->getCurrencyCode()
        );
    }

    public static function toBrick(MoneyInterface $money): BrickMoney
    {
        return BrickMoney::ofMinor($money->getAmount(), $money->getCurrency());
    }

    /**
     * Adds `a` to `b`.
     */
    public function add(MoneyInterface $a, MoneyInterface $b): Money
    {
        $a = $this->convert($a, $b->getCurrency());
        $ab = self::toBrick($b)->plus($a);

        return self::toMoney($ab);
    }

    /**
     * Substracts `a` from `b`.
     */
    public function substract(MoneyInterface $a, MoneyInterface $b): Money
    {
        $a = $this->convert($a, $b->getCurrency());
        $ab = self::toBrick($b)->minus($a);

        return self::toMoney($ab);
    }

    /**
     * Compares if one Money is of less value than other.
     *
     * @return bool `true` if `$money` is less than `$than`
     */
    public function isLess(MoneyInterface $money, MoneyInterface $than): bool
    {
        $money = $this->convert($money, $than->getCurrency());

        return $money->isLessThan(self::toBrick($than));
    }

    /**
     * Compares if one Money is of greater or equal value than other.
     *
     * @return bool `true` if `$money` is more than or same as `$than`
     */
    public function isMoreOrSame(MoneyInterface $money, MoneyInterface $than): bool
    {
        $money = $this->convert($money, $than->getCurrency());

        return $money->isGreaterThanOrEqualTo(self::toBrick($than));
    }

    private function convert(MoneyInterface $money, string $toCurrency): BrickMoney
    {
        $fromCurrency = $money->getCurrency();
        if ($fromCurrency === $toCurrency) {
            return self::toBrick($money);
        }

        $exchange = $this->exchangeLocator->get($fromCurrency, $toCurrency);

        return self::toBrick($exchange->convert($money, $toCurrency));
    }
}
