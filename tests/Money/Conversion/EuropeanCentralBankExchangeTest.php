<?php

namespace App\Tests\Money\Conversion;

use App\Money\Conversion\Exchange\EuropeanCentralBankExchange;
use App\Money\Money;
use Brick\Money\Context\CustomContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class EuropeanCentralBankExchangeTest extends TestCase
{
    public function testGetsData()
    {
        $exchange = new EuropeanCentralBankExchange();
        $exchangeData = $exchange->getData();

        $this->assertIsArray($exchangeData);
        $this->assertArrayHasKey('Cube', $exchangeData);
        $this->assertArrayHasKey('@attributes', $exchangeData);
    }

    public function testStoresDataInCache()
    {
        $exchange = new EuropeanCentralBankExchange();
        $cache = new FilesystemAdapter();

        $exchangeData = $cache->get($exchange->getName(), function (): false {
            return false;
        });

        $this->assertNotFalse($exchangeData);
        $this->assertIsArray($exchangeData);
        $this->assertArrayHasKey('Cube', $exchangeData);
        $this->assertArrayHasKey('@attributes', $exchangeData);
    }

    public function testConvertsWholeEuros()
    {
        $exchange = new EuropeanCentralBankExchange();
        $context = new CustomContext(scale: 0, step: 1);

        $this->assertEquals(100, $exchange->convert(new Money(100, 'JPY'), 'EUR', $context)->getAmount());
        $this->assertEquals(100, $exchange->convert(new Money(150, 'JPY'), 'EUR', $context)->getAmount());
        $this->assertEquals(100, $exchange->convert(new Money(700, 'CNY'), 'EUR', $context)->getAmount());
    }
}
