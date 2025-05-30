<?php

namespace App\Entity\Gateway;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use App\Entity\Money;
use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\TimestampedUpdationEntity;
use App\Gateway\ChargeStatus;
use App\Gateway\ChargeType;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Gateway\ChargeRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCharge represents a monetary payment that can be done by an issuer at checkout with the Gateway.
 */
#[MapProvider(EntityMapProvider::class)]
#[Gedmo\Loggable()]
#[ORM\Table(name: 'checkout_charge')]
#[ORM\Entity(repositoryClass: ChargeRepository::class)]
class Charge
{
    use TimestampedCreationEntity;
    use TimestampedUpdationEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'charges')]
    private ?Checkout $checkout = null;

    /**
     * The type represents the kind of payment for the charged money.
     */
    #[Assert\NotBlank()]
    #[ORM\Column()]
    private ?ChargeType $type = null;

    /**
     * A short, descriptive text for this charge operation.
     */
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * Detailed message about this charge operation.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * The Accounting receiving the consequent Transaction for this GatewayCharge.
     */
    #[Assert\NotBlank()]
    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $target = null;

    /**
     * The charged monetary sum.
     */
    #[Assert\NotBlank()]
    #[ORM\Embedded(Money::class)]
    private ?Money $money = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\JoinTable(name: 'checkout_charge_transaction')]
    #[ORM\ManyToMany(targetEntity: Transaction::class, cascade: ['persist'])]
    private Collection $transactions;

    /**
     * The status of the charge with the Gateway.
     */
    #[Gedmo\Versioned]
    #[ORM\Column()]
    private ?ChargeStatus $status = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->status = ChargeStatus::InPending;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheckout(): ?Checkout
    {
        return $this->checkout;
    }

    public function setCheckout(?Checkout $checkout): static
    {
        $this->checkout = $checkout;

        return $this;
    }

    public function getType(): ?ChargeType
    {
        return $this->type;
    }

    public function setType(ChargeType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTarget(): ?Accounting
    {
        return $this->target;
    }

    public function setTarget(?Accounting $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getMoney(): ?Money
    {
        return $this->money;
    }

    public function setMoney(Money $money): static
    {
        $this->money = $money;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        $this->transactions->removeElement($transaction);

        return $this;
    }

    public function getStatus(): ?ChargeStatus
    {
        return $this->status;
    }

    public function setStatus(ChargeStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
