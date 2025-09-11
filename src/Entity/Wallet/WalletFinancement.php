<?php

namespace App\Entity\Wallet;

use App\Entity\EmbeddableMoney;
use App\Repository\Wallet\WalletFinancementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Financement represents how much money was moved from incoming to outgoing Wallet Statements.
 */
#[ORM\Entity(repositoryClass: WalletFinancementRepository::class)]
class WalletFinancement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The monetary value used in this Financement.
     */
    #[ORM\Embedded(class: EmbeddableMoney::class)]
    private ?EmbeddableMoney $money = null;

    /**
     * An incoming Statement that originally saved the money in this Financement.
     */
    #[ORM\ManyToOne(inversedBy: 'financesTo')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WalletStatement $origin = null;

    /**
     * An outgoing Statement that eventually spent the money in this Financement.
     */
    #[ORM\ManyToOne(inversedBy: 'financedBy')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WalletStatement $target = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMoney(): ?EmbeddableMoney
    {
        return $this->money;
    }

    public function setMoney(EmbeddableMoney $money): static
    {
        $this->money = $money;

        return $this;
    }

    public function getOrigin(): ?WalletStatement
    {
        return $this->origin;
    }

    public function setOrigin(?WalletStatement $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getTarget(): ?WalletStatement
    {
        return $this->target;
    }

    public function setTarget(?WalletStatement $target): static
    {
        $this->target = $target;

        return $this;
    }
}
