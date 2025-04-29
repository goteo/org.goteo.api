<?php

namespace App\Service;

use App\ApiResource\Accounting\AccountingBalancePoint;
use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
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
        $trxs = $this->transactionRepository->findByAccounting($accounting);

        foreach ($trxs as $transaction) {
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
     * Applies a transaction to a given balance depending on its origin/target.
     */
    private function applyTransactionToBalance(Money $balance, Transaction $trx, Accounting $accounting): Money
    {
        $trxMoney = $trx->getMoney();

        if ($trx->getTarget() === $accounting) {
            return $this->money->add($trxMoney, $balance);
        }

        if ($trx->getOrigin() === $accounting) {
            return $this->money->substract($trxMoney, $balance);
        }

        return $balance;
    }

    /**
     * Calculates an AccountingBalancePoint series for a given Accounting over a period of time.
     *
     * @param Accounting  $accounting The Accounting to calc the data series for
     * @param \DatePeriod $period     A period of time for the desired transactions and time unit of the serie
     * @param bool        $aggregate  if true, balances accumulate over time; otherwise, they are interval-specific
     *
     * @return array<int, AccountingBalancePoint> One AccountingBalancePoint for each interval inside the date period range
     */
    public function calcBalancePoints(
        Accounting $accounting,
        \DatePeriod $period,
        bool $aggregate = false,
    ): array {
        $trxs = $this->transactionRepository->findByAccounting(
            $accounting,
            $period->getStartDate(),
            $period->getEndDate(),
        );
        $points = [];

        $totalBalance = new Money(0, $accounting->getCurrency());

        foreach ($period as $lowerBound) {
            $upperBound = \DateTime::createFromInterface($lowerBound);
            $upperBound->add($period->getDateInterval());

            /** @var Transaction[] */
            $periodTrxs = [...\array_filter($trxs, function (Transaction $trx) use ($lowerBound, $upperBound) {
                return $trx->getDateCreated() >= $lowerBound
                    && $trx->getDateCreated() < $upperBound;
            })];

            $balance = $aggregate ? $totalBalance : new Money(0, $accounting->getCurrency());

            foreach ($periodTrxs as $trx) {
                $balance = $this->applyTransactionToBalance($balance, $trx, $accounting);
            }

            if ($aggregate) {
                $totalBalance = $balance;
            }

            $point = new AccountingBalancePoint();
            $point->start = $lowerBound;
            $point->end = $upperBound;
            $point->balance = $balance;
            $point->length = count($periodTrxs);

            $points[] = $point;
        }

        return $points;
    }
}
