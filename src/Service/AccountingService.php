<?php

namespace App\Service;

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
     * Calculates a balance data series for a given Accounting over a period of time.
     *
     * @param Accounting  $accounting The Accounting to calc the data series for
     * @param \DatePeriod $period     A period of time for the desired transactions and time unit of the serie
     *
     * @return array<int, Money> A series of aggregated balances
     */
    public function calcBalanceSeries(
        Accounting $accounting,
        \DatePeriod $period,
    ): array {
        $trxs = $this->transactionRepository->findByAccounting(
            $accounting,
            $period->getStartDate(),
            $period->getEndDate(),
            true
        );

        /** @var Transaction[][] */
        $intervals = [];

        foreach ($period as $lowerBound) {
            $upperBound = \DateTime::createFromInterface($lowerBound);
            $upperBound->add($period->getDateInterval());

            $intervals[] = [...\array_filter($trxs, function (Transaction $trx) use ($lowerBound, $upperBound) {
                return $trx->getDateCreated() >= $lowerBound
                    && $trx->getDateCreated() < $upperBound;
            })];
        }

        /** @var Money[] */
        $points = [];

        foreach ($intervals as $interval) {
            $point = new Money(0, $accounting->getCurrency());

            foreach ($interval as $trx) {
                if ($trx->getTarget() === $accounting) {
                    $point = $this->money->add($trx->getMoney(), $point);
                }

                if ($trx->getOrigin() === $accounting) {
                    $point = $this->money->substract($trx->getMoney(), $point);
                }
            }

            if (!empty($points)) {
                $point = $this->money->add(\end($points), $point);
            }

            $points[] = $point;
        }

        return $points;
    }
}
