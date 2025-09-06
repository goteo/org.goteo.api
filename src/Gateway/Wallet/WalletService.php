<?php

namespace App\Gateway\Wallet;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use App\Entity\EmbeddableMoney;
use App\Entity\Wallet\WalletFinancement;
use App\Entity\Wallet\WalletStatement;
use App\Money\Money;
use App\Money\MoneyService;
use App\Repository\Wallet\WalletStatementRepository;
use Brick\Money as Brick;
use Doctrine\ORM\EntityManagerInterface;

class WalletService
{
    public function __construct(
        private MoneyService $money,
        private WalletStatementRepository $statementRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return WalletStatement[]
     */
    public function getStatements(Accounting $accounting): array
    {
        return $this->statementRepository->findByAccounting($accounting);
    }

    public function getBalance(Accounting $accounting): Money
    {
        $total = Brick\Money::ofMinor(0, $accounting->getCurrency());
        $statements = $this->statementRepository->findByTarget($accounting);

        foreach ($statements as $statement) {
            $balance = $this->money->toBrick($statement->getBalance());

            $total = $total->plus($balance);
        }

        return new Money(
            $total->getMinorAmount()->toInt(),
            $total->getCurrency()->getCurrencyCode()
        );
    }

    /**
     * Obtain an `incoming` WalletStatement for the Transaction.
     *
     * @param Transaction $transaction The Transaction targetting a wallet
     *
     * @return WalletStatement An incoming statement for the transaction
     */
    public function save(Transaction $transaction): WalletStatement
    {
        $statement = $this->statementRepository->findByTransaction($transaction);

        if ($statement) {
            return $statement;
        }

        $statement = new WalletStatement();
        $statement->setTransaction($transaction);
        $statement->setDirection(StatementDirection::Incoming);
        $statement->setBalance($transaction->getMoney());

        return $statement;
    }

    /**
     * Takes the money of a Transaction from the origin wallet.
     *
     * @param Transaction $transaction The Transaction originating from a wallet
     *
     * @return WalletStatement An outgoing statement financed by previous incoming statements
     */
    public function spend(Transaction $transaction): WalletStatement
    {
        $origin = $transaction->getOrigin();

        $spendGoal = $transaction->getMoney();
        $spentTotal = new Money(0, $spendGoal->getCurrency());

        $outgoing = new WalletStatement();
        $outgoing->setTransaction($transaction);
        $outgoing->setDirection(StatementDirection::Outgoing);

        $statements = $this->getStatements($origin);
        foreach ($statements as $statement) {
            if ($spendGoal->getAmount() === 0) {
                break;
            }

            if (!$statement->hasDirection(StatementDirection::Incoming)) {
                continue;
            }

            $incoming = $statement;

            $balance = $incoming->getBalance();
            if ($balance->getAmount() === 0) {
                continue;
            }

            if ($this->money->isLess($spendGoal, $balance)) {
                $balanceSpent = $spendGoal;
            } else {
                $balanceSpent = $balance;
            }

            $financement = new WalletFinancement();
            $financement->setMoney(EmbeddableMoney::of($balanceSpent));

            $substracted = $this->money->substract($balanceSpent, $balance);
            $incoming->setBalance(EmbeddableMoney::of($substracted));
            $incoming->addFinancesTo($financement);

            $added = $this->money->add($balanceSpent, $spentTotal);
            $outgoing->setBalance(EmbeddableMoney::of($added));
            $outgoing->addFinancedBy($financement);

            $this->entityManager->persist($incoming);

            $spendGoal = $this->money->substract($balanceSpent, $spendGoal);
            $spentTotal = $this->money->add($balanceSpent, $spentTotal);
        }

        return $outgoing;
    }
}
