<?php

namespace App\Entity\Gateway;

use App\Entity\Accounting\Accounting;
use App\Entity\Trait\MigratedEntity;
use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\TimestampedUpdationEntity;
use App\Gateway\CheckoutStatus;
use App\Gateway\Link;
use App\Gateway\RefundStrategy;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Gateway\CheckoutRepository;
use App\Validator\SupportedChargeTypes;
use AutoMapper\Attribute\MapProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCheckout bundles the data to perform a payment operation with a Gateway.\
 * \
 * Once the Gateway validates the payment as successful the GatewayCheckout will be updated
 * and respective AccountingTransactions will be generated for each Charge.
 */
#[MapProvider(EntityMapProvider::class)]
#[Gedmo\Loggable()]
#[ORM\Entity(repositoryClass: CheckoutRepository::class)]
#[ORM\Index(fields: ['migratedId'])]
class Checkout
{
    use MigratedEntity;
    use TimestampedCreationEntity;
    use TimestampedUpdationEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The name of the Gateway implementation to checkout with.
     */
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $gatewayName = null;

    /**
     * The Accounting that will issue the Transactions of the GatewayCharges after a successful checkout.
     */
    #[Assert\NotBlank()]
    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $origin = null;

    /**
     * The GatewayCharges to be charged at checkout with the gateway.
     *
     * @var Collection<int, Charge>
     */
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1)]
    #[SupportedChargeTypes()]
    #[ORM\OneToMany(mappedBy: 'checkout', targetEntity: Charge::class, cascade: ['persist'])]
    private Collection $charges;

    /**
     * The strategy to refund the payment.
     */
    #[ORM\Column(enumType: RefundStrategy::class)]
    private ?RefundStrategy $refundStrategy = RefundStrategy::ToWallet;

    /**
     * The address to where the user must be redirected to.
     */
    #[Assert\NotBlank()]
    #[Assert\Url()]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $returnUrl = null;

    /**
     * The status of the checkout with the Gateway.
     */
    #[Gedmo\Versioned]
    #[ORM\Column()]
    private ?CheckoutStatus $status = null;

    /**
     * A list of URLs provided by the Gateway for this checkout.\
     * e.g: Fulfill payment, API resource address.
     *
     * @var Link[]
     */
    #[ORM\Column]
    private array $links = [];

    /**
     * @var Collection<int, Tracking>
     */
    #[ORM\OneToMany(targetEntity: Tracking::class, mappedBy: 'checkout', cascade: ['persist'])]
    private Collection $trackings;

    public function __construct()
    {
        $this->status = CheckoutStatus::InPending;
        $this->charges = new ArrayCollection();
        $this->trackings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGatewayName(): ?string
    {
        return $this->gatewayName;
    }

    public function setGatewayName(string $gatewayName): static
    {
        $this->gatewayName = $gatewayName;

        return $this;
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

    /**
     * @return Collection<int, Charge>
     */
    public function getCharges(): Collection
    {
        return $this->charges;
    }

    /**
     * @param Collection<int, Charge> $charges
     */
    public function setCharges(Collection $charges): static
    {
        $this->charges = new ArrayCollection();

        foreach ($charges as $charge) {
            $this->charges->add($charge);
            $charge->setCheckout($this);
        }

        return $this;
    }

    public function addCharge(Charge $charge): static
    {
        if (!$this->charges->contains($charge)) {
            $this->charges->add($charge);
            $charge->setCheckout($this);
        }

        return $this;
    }

    public function removeCharge(Charge $charge): static
    {
        if ($this->charges->removeElement($charge)) {
            // set the owning side to null (unless already changed)
            if ($charge->getCheckout() === $this) {
                $charge->setCheckout(null);
            }
        }

        return $this;
    }

    public function getRefundStrategy(): ?RefundStrategy
    {
        return $this->refundStrategy;
    }

    public function setRefundStrategy(?RefundStrategy $refundStrategy): static
    {
        $this->refundStrategy = $refundStrategy;

        return $this;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    public function setReturnUrl(string $returnUrl): static
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    public function isCharged(): bool
    {
        return $this->status === CheckoutStatus::Charged;
    }

    public function getStatus(): ?CheckoutStatus
    {
        return $this->status;
    }

    public function setStatus(CheckoutStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param Link[] $links
     */
    public function setLinks(array $links): static
    {
        $this->links = $links;

        return $this;
    }

    public function addLink(Link $link): static
    {
        $this->removeLink($link);

        $this->links = [...$this->links, $link];

        return $this;
    }

    public function removeLink(Link $link): static
    {
        $this->links = \array_filter(
            \array_map(fn($l) => Link::tryFrom($l), $this->links),
            function (Link $existingLink) use ($link) {
                return $existingLink->href !== $link->href;
            }
        );

        return $this;
    }

    /**
     * @return Collection<int, Tracking>
     */
    public function getTrackings(): Collection
    {
        return $this->trackings;
    }

    public function addTracking(Tracking $tracking): static
    {
        if (!$this->trackings->contains($tracking)) {
            $this->trackings->add($tracking);
            $tracking->setCheckout($this);
        }

        return $this;
    }

    public function removeTracking(Tracking $tracking): static
    {
        if ($this->trackings->removeElement($tracking)) {
            // set the owning side to null (unless already changed)
            if ($tracking->getCheckout() === $this) {
                $tracking->setCheckout(null);
            }
        }

        return $this;
    }
}
