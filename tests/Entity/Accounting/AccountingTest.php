<?php

namespace App\Tests\Entity\Accounting;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use App\Entity\EmbeddableMoney;
use App\Entity\Tipjar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class AccountingTest extends KernelTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAccountingGetsUpdatedByCascade()
    {
        $tipjarA = new Tipjar();
        $tipjarA->setName('TEST_TIPJAR_A');

        $accountingA = new Accounting();
        $accountingA->setOwner($tipjarA);

        $this->entityManager->persist($tipjarA);
        $this->entityManager->flush();

        /** @var Accounting */
        $accountingA = $this->entityManager->getRepository(Tipjar::class)
            ->findOneBy(['name' => 'TEST_TIPJAR_A'])
            ->getAccounting();

        $this->assertNotNull($accountingA->getId());

        $this->assertSame($tipjarA, $accountingA->getOwner());
        $this->assertSame($tipjarA, $accountingA->getTipjar());
        $this->assertEquals($tipjarA->getId(), $accountingA->getOwner()->getId());

        $this->assertEquals(null, $accountingA->getUser());
        $this->assertEquals(null, $accountingA->getProject());

        $tipjarB = new Tipjar();
        $tipjarB->setName('TEST_TIPJAR_B');

        $accountingB = new Accounting();
        $accountingB->setOwner($tipjarB);

        $this->entityManager->persist($tipjarB);
        $this->entityManager->flush();

        /** @var Accounting */
        $accountingB = $this->entityManager->getRepository(Tipjar::class)
            ->findOneBy(['name' => 'TEST_TIPJAR_B'])
            ->getAccounting();

        $this->assertNotNull($accountingB->getId());

        $this->assertSame($tipjarB, $accountingB->getOwner());
        $this->assertNotSame($tipjarA, $accountingB->getOwner());
        $this->assertSame($tipjarB, $accountingB->getTipjar());
        $this->assertNotSame($tipjarA, $accountingB->getTipjar());

        $this->assertEquals($tipjarB->getId(), $accountingB->getOwner()->getId());
        $this->assertNotEquals($tipjarA->getId(), $accountingB->getOwner()->getId());

        $this->assertEquals(null, $accountingB->getUser());
        $this->assertEquals(null, $accountingB->getProject());
    }

    public function testTransactionUpdatesBalances()
    {
        $tipjarA = new Tipjar();
        $tipjarA->setName('TEST_TIPJAR_A');

        $origin = Accounting::of($tipjarA);
        $origin->setCurrency('EUR');
        $origin->setBalance(new EmbeddableMoney(1000, 'EUR'));

        $tipjarB = new Tipjar();
        $tipjarB->setName('TEST_TIPJAR_B');

        $target = Accounting::of($tipjarB);
        $target->setCurrency('EUR');
        $target->setBalance(new EmbeddableMoney(500, 'EUR'));

        $this->entityManager->persist($origin);
        $this->entityManager->persist($target);
        $this->entityManager->flush();

        $transaction = new Transaction();
        $transaction
            ->setOrigin($origin)
            ->setTarget($target)
            ->setMoney(new EmbeddableMoney(200, 'EUR'))
        ;

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        $this->entityManager->refresh($origin);
        $this->entityManager->refresh($target);

        $this->assertSame(800, $origin->getBalance()->getAmount());
        $this->assertSame(700, $target->getBalance()->getAmount());
    }
}
