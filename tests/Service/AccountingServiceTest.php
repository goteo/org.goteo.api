<?php

namespace App\Tests\Service;

use App\Entity\Accounting\Transaction;
use App\Entity\Money;
use App\Entity\Tipjar;
use App\Service\AccountingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class AccountingServiceTest extends KernelTestCase
{
    use ResetDatabase;

    private AccountingService $accountingService;
    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->accountingService = static::getContainer()->get(AccountingService::class);
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function getBalancedAccountings(): array
    {
        $tipjars = [];

        for ($i = 0; $i < 10; ++$i) {
            $tipjar = new Tipjar();
            $tipjar->setName(\sprintf('TEST_TIPJAR_%d', $i));

            $this->entityManager->persist($tipjar);

            $tipjars[] = $tipjar;
        }

        $this->entityManager->flush();

        for ($i = 0; $i < \count($tipjars) * 5; ++$i) {
            $origin = $i % 2 ? $tipjars[\random_int(0, 5)] : $tipjars[\random_int(5, 9)];
            $target = $i % 2 ? $tipjars[\random_int(5, 9)] : $tipjars[\random_int(0, 5)];

            $trx = new Transaction();
            $trx->setMoney(new Money(random_int(100, 999), 'EUR'));
            $trx->setOrigin($origin->getAccounting());
            $trx->setTarget($target->getAccounting());

            $this->entityManager->persist($trx);
        }

        $this->entityManager->flush();

        return \array_map(function (Tipjar $tipjar) {
            return $tipjar->getAccounting();
        }, $tipjars);
    }

    public function testBalanceSerieNotOverLength()
    {
        $accountings = $this->getBalancedAccountings();

        foreach ($accountings as $accounting) {
            $serie = $this->accountingService->calcBalanceSerie($accounting, maxLength: 3);

            $this->assertLessThanOrEqual(3, \count($serie));

            foreach ($serie as $dataPoint) {
                $this->assertInstanceOf(Money::class, $dataPoint);
                $this->assertGreaterThan(0, $dataPoint->amount);
            }
        }
    }
}
