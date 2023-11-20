<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Library\Economy\Monetizable;
use App\Repository\AccountingOutgoingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Accounting Outgoings represent an issuing of funds.
 * Outgoings are generated by the Accounting when asked to originate a Transaction
 * after it secured the funds from the available Incomings.
 */
#[ORM\Entity(repositoryClass: AccountingOutgoingRepository::class)]
#[API\ApiResource(
    uriTemplate: '/accountings/{account}/outgoings/{id}',
    uriVariables: [
        'account' => new API\Link(fromClass: Accounting::class, fromProperty: 'id'),
        'id' => new API\Link(fromClass: AccountingOutgoing::class)
    ],
    operations: [new API\Get()]
)]
class AccountingOutgoing extends Monetizable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Amount leaving the Accounting.
     * Expressed in the minor unit of the currency (cents, pennies, etc)
     */
    #[ORM\Column]
    protected int $amount = 0;

    /**
     * 3-letter ISO 4217 currency code. Same as parent Accounting.
     */
    #[ORM\Column(length: 3)]
    protected string $currency = "";

    #[ORM\ManyToOne(inversedBy: 'outgoing')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $accounting = null;

    /**
     * The Transaction that generated this Outgoing.
     */
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Transaction $transaction = null;

    /**
     * When a Transaction asks for a different currency an exchange operation will be performed.\
     * Original amount, currency and exchange rate data can be referenced here.
     */
    #[ORM\Embedded(class: TransactionExchange::class)]
    private ?object $transactionExchange = null;

    /**
     * A list of Accounting Fundings detailing the Incomings that secured the funds.
     */
    #[ORM\OneToMany(mappedBy: 'outgoing', targetEntity: AccountingFunding::class)]
    private Collection $financedBy;

    public function __construct()
    {
        $this->financedBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAccounting(): ?Accounting
    {
        return $this->accounting;
    }

    public function setAccounting(?Accounting $accounting): static
    {
        $this->accounting = $accounting;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getTransactionExchange(): ?object
    {
        return $this->transactionExchange;
    }

    public function setTransactionExchange(?object $transactionExchange): static
    {
        $this->transactionExchange = $transactionExchange;

        return $this;
    }

    /**
     * @return Collection<int, AccountingFunding>
     */
    public function getFinancedBy(): Collection
    {
        return $this->financedBy;
    }

    public function addFinancedBy(AccountingFunding $financedBy): static
    {
        if (!$this->financedBy->contains($financedBy)) {
            $this->financedBy->add($financedBy);
            $financedBy->setOutgoing($this);
        }

        return $this;
    }

    public function removeFinancedBy(AccountingFunding $financedBy): static
    {
        if ($this->financedBy->removeElement($financedBy)) {
            // set the owning side to null (unless already changed)
            if ($financedBy->getOutgoing() === $this) {
                $financedBy->setOutgoing(null);
            }
        }

        return $this;
    }
}
