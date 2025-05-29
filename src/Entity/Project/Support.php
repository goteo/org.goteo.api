<?php

namespace App\Entity\Project;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Project\SupportRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Table(name: 'project_support')]
#[ORM\Entity(repositoryClass: SupportRepository::class)]
class Support
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'supports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $origin = null;

    #[ORM\ManyToOne(inversedBy: 'supports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'support')]
    private Collection $transactions;

    #[ORM\Column]
    private ?bool $matchfunding = null;

    #[ORM\Column]
    private ?bool $anonymous = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $message = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrigin(): ?Accounting
    {
        return $this->origin;
    }

    public function setOrigin(?Accounting $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setSupport($this);
        }

        return $this;
    }

    /**
     * @param Transaction[] $transactions
     */
    public function addTransactions(array $transactions): static
    {
        foreach ($transactions as $transaction) {
            $this->addTransaction($transaction);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getSupport() === $this) {
                $transaction->setSupport(null);
            }
        }

        return $this;
    }

    public function isMatchfunding(): ?bool
    {
        return $this->matchfunding;
    }

    public function setMatchfunding(bool $matchfunding): static
    {
        $this->matchfunding = $matchfunding;

        return $this;
    }

    public function isAnonymous(): ?bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): static
    {
        $this->anonymous = $anonymous;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
