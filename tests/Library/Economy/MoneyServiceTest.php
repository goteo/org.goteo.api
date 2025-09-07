<?php

namespace App\Tests\Library\Economy;

use App\Money\Money;
use App\Money\MoneyService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * WARNING: This test compares currencies, which change over time
 * and might cause the test to fail without justifiable cause in the code.
 *
 * Test currencies and values have been selected for their stability to minimize chances of external factors such as
 * * Hyper inflation/deflation
 * * Currency intervention
 * * Demonetization
 */
class MoneyServiceTest extends KernelTestCase
{
    private MoneyService $moneyService;

    public function setUp(): void
    {
        self::bootKernel();

        $this->moneyService = static::getContainer()->get(MoneyService::class);
    }

    public function testComparesLess()
    {
        $more = new Money(300, 'EUR');
        $less = new Money(200, 'EUR');

        $this->assertTrue($this->moneyService->isLess($less, $more));
        $this->assertFalse($this->moneyService->isLess($more, $less));

        // We are cooked if this fails and I'm not talking about code
        $this->assertTrue($this->moneyService->isLess(new Money(100, 'JPY'), $more));
    }

    public function testComparesMore()
    {
        $a = new Money(100, 'GBP');
        $b = new Money(100, 'GBP');

        $this->assertTrue($this->moneyService->isMoreOrSame($a, $b));
        $this->assertTrue($this->moneyService->isMoreOrSame($b, $a));

        $c = new Money(300, 'GBP');

        $this->assertTrue($this->moneyService->isMoreOrSame($c, $b));
        $this->assertFalse($this->moneyService->isMoreOrSame($b, $c));

        $d = new Money(100, 'MXN');

        $this->assertFalse($this->moneyService->isMoreOrSame($d, $c));
        $this->assertFalse($this->moneyService->isMoreOrSame($d, $a));
    }

    public function testAddition()
    {
        $a = new Money(100, 'EUR');
        $b = new Money(110, 'EUR');

        $c = $this->moneyService->add($a, $b);

        $this->assertEquals(100, $a->getAmount());
        $this->assertEquals(110, $b->getAmount());
        $this->assertEquals(210, $c->getAmount());

        $d = new Money(100, 'USD');
        $e = $this->moneyService->add($a, $d);

        $this->assertEquals($d->getAmount(), $a->getAmount());
        $this->assertEquals($d->getCurrency(), $e->getCurrency());

        $this->assertNotEquals($d->getAmount(), $e->getAmount());
        $this->assertTrue($this->moneyService->isMoreOrSame($e, $d));
        $this->assertTrue($this->moneyService->isMoreOrSame($e, $a));
    }

    public function testSubstraction()
    {
        $a = new Money(101, 'EUR');
        $b = new Money(210, 'EUR');

        $c = $this->moneyService->substract($a, $b);

        $this->assertEquals(101, $a->getAmount());
        $this->assertEquals(210, $b->getAmount());
        $this->assertEquals(109, $c->getAmount());

        $d = new Money(500, 'USD');
        $e = $this->moneyService->substract($a, $d);

        $this->assertEquals($d->getCurrency(), $e->getCurrency());

        $this->assertNotEquals($d->getAmount(), $e->getAmount());
        $this->assertTrue($this->moneyService->isLess($e, $d));
        $this->assertTrue($this->moneyService->isMoreOrSame($e, $a));
    }
}
