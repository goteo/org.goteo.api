<?php

namespace App\Entity\Gateway;

use App\Mapping\Provider\TrackingMapProvider;
use App\Repository\Gateway\TrackingRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\ORM\Mapping as ORM;

#[MapProvider(TrackingMapProvider::class)]
#[ORM\Table(name: 'checkout_tracking')]
#[ORM\Entity(repositoryClass: TrackingRepository::class)]
class Tracking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trackings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Checkout $checkout = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    public function __construct(
        ?string $title = null,
        ?string $value = null,
    ) {
        $this->title = $title ?? $title;
        $this->value = $value ?? $value;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
