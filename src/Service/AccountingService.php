<?php

namespace App\Service;

use App\Entity\Accounting\Accounting;
use App\Entity\Money;
use App\Entity\User\User;
use App\Gateway\Wallet\WalletService;
use App\Library\Economy\MoneyService;
use App\Repository\Accounting\TransactionRepository;

class AccountingService
{
    public function __construct(
        private MoneyService $money,
        private WalletService $wallet,
        private TransactionRepository $transactionRepository,
    ) {}

    public function calcBalance(Accounting $accounting): Money
    {
        $owner = $accounting->getOwner();

        if ($owner instanceof User) {
            return $this->wallet->getBalance($accounting);
        }

        $balance = new Money(0, $accounting->getCurrency());
        $transactions = $this->transactionRepository->findByAccounting($accounting);

        foreach ($transactions as $transaction) {
            if ($transaction->getTarget() === $accounting) {
                $balance = $this->money->add($transaction->getMoney(), $balance);
            }

            if ($transaction->getOrigin() === $accounting) {
                $balance = $this->money->substract($transaction->getMoney(), $balance);
            }
        }

        return $balance;
    }

    /**
     * Calculates a balance data series for a given Accounting over a period of time.
     *
     * @param Accounting The Accounting to calc the data series for
     * @param \DateTimeInterface|null The date to start calculating from
     * @param \DateTimeInterface|null The date to calculate up to. Inclusive
     * @param int $maxLength The max number of data points to include in the returned series
     *
     * @return array<int, Money> a series of aggregated balances, up to $maxLength in size
     */
    public function calcBalanceSerie(
        Accounting $accounting,
        ?\DateTimeInterface $dateStart = null,
        ?\DateTimeInterface $dateEnd = null,
        int $maxLength = 10,
    ): array {
        $transactions = $this->transactionRepository->findByAccounting($accounting, $dateStart, $dateEnd, true);

        $dataPointLength = \ceil(\count($transactions) / $maxLength);
        $dataPointLength = $dataPointLength < 1 ? 1 : $dataPointLength;

        $dataPointItems = \array_chunk($transactions, $dataPointLength);

        /** @var Money[] */
        $dataPoints = [];
        foreach ($dataPointItems as $dataPointItem) {
            $dataPointBalance = $dataPointItem[0]->getMoney();

            if (!empty($dataPoints)) {
                $dataPointBalance = $this->money->add(\end($dataPoints), $dataPointBalance);
            }

            foreach (\array_slice($dataPointItem, 1) as $trx) {
                $dataPointBalance = $this->money->add($trx->getMoney(), $dataPointBalance);
            }

            $dataPoints[] = $dataPointBalance;
        }

        return $dataPoints;
    }
}
