<?php

namespace App\Tests\Money\Conversion;

use App\Money\Conversion\Exchange\EuropeanCentralBankExchange;
use App\Money\Money;
use Brick\Money\Context\CustomContext;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class EuropeanCentralBankExchangeTest extends KernelTestCase
{
    private EuropeanCentralBankExchange $exchange;

    public function setUp(): void
    {
        self::bootKernel();

        $this->exchange = static::getContainer()->get(EuropeanCentralBankExchange::class);
    }

    public function testGetsData()
    {
        $exchangeData = $this->exchange->getData();

        $this->assertIsArray($exchangeData);
        $this->assertArrayHasKey('Cube', $exchangeData);
        $this->assertArrayHasKey('@attributes', $exchangeData);
    }

    public function testStoresDataInCache()
    {
        $cache = new FilesystemAdapter();

        $exchangeData = $cache->get($this->exchange->getName(), function (): false {
            return false;
        });

        $this->assertNotFalse($exchangeData);
        $this->assertIsArray($exchangeData);
        $this->assertArrayHasKey('Cube', $exchangeData);
        $this->assertArrayHasKey('@attributes', $exchangeData);
    }

    public function testConvertsWholeEuros()
    {
        $context = new CustomContext(scale: 0, step: 1);

        $this->assertEquals(100, $this->exchange->convert(new Money(100, 'JPY'), 'EUR', $context)->getAmount());
        $this->assertEquals(100, $this->exchange->convert(new Money(150, 'JPY'), 'EUR', $context)->getAmount());
        $this->assertEquals(100, $this->exchange->convert(new Money(700, 'CNY'), 'EUR', $context)->getAmount());
    }
}
