<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GatewayChargeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCharge represents a monetary payment that can be done by an issuer at checkout with the Gateway.
 */
#[ORM\Entity(repositoryClass: GatewayChargeRepository::class)]
#[ApiResource]
class GatewayCharge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank()]
    #[ORM\Column()]
    private ?GatewayChargeType $type = null;

    #[Assert\NotBlank()]
    #[ORM\Embedded(Money::class)]
    private ?Money $money = null;

    #[Assert\NotBlank()]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $target = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?GatewayChargeType
    {
        return $this->type;
    }

    public function setType(GatewayChargeType $type): static
    {
        $this->type = $type;

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

    public function getTarget(): ?Accounting
    {
        return $this->target;
    }

    public function setTarget(?Accounting $target): static
    {
        $this->target = $target;

        return $this;
    }
}
