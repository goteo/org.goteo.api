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
     * Calculates an AccountingBalancePoint series for a given Accounting over a period of time.
     *
     * @param Accounting  $accounting The Accounting to calc the data series for
     * @param \DatePeriod $period     A period of time for the desired transactions and time unit of the serie
     *
     * @return array<int, AccountingBalancePoint> One AccountingBalancePoint for each interval inside the date period range
     */
    public function calcBalancePoints(
        Accounting $accounting,
        \DatePeriod $period,
    ): array {
        $trxs = $this->transactionRepository->findByAccounting(
            $accounting,
            $period->getStartDate(),
            $period->getEndDate(),
        );
        $points = [];

        foreach ($period as $lowerBound) {
            $upperBound = \DateTime::createFromInterface($lowerBound);
            $upperBound->add($period->getDateInterval());

            /** @var Transactions[] */
            $periodTrxs = [...\array_filter($trxs, function (Transaction $trx) use ($lowerBound, $upperBound) {
                return $trx->getDateCreated() >= $lowerBound
                    && $trx->getDateCreated() < $upperBound;
            })];

            $balance = new Money(0, $accounting->getCurrency());

            foreach ($periodTrxs as $trx) {
                if ($trx->getTarget() === $accounting) {
                    $balance = $this->money->add($trx->getMoney(), $balance);
                }

                if ($trx->getOrigin() === $accounting) {
                    $balance = $this->money->substract($trx->getMoney(), $balance);
                }
            }

            $point = new AccountingBalancePoint();
            $point->start = $lowerBound;
            $point->end = $upperBound;
            $point->balance = $balance;
            $point->length = \count($periodTrxs);

            $points[] = $point;
        }

        return $points;
    }
}
