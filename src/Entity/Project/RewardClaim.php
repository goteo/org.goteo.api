<?php

namespace App\Entity\Project;

use App\Entity\Gateway\Charge;
use App\Entity\Interface\UserOwnedInterface;
use App\Entity\Trait\UserOwnedTrait;
use App\Entity\User\User;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Project\RewardClaimRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\ORM\Mapping as ORM;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Table(name: 'project_reward_claim')]
#[ORM\Entity(repositoryClass: RewardClaimRepository::class)]
class RewardClaim implements UserOwnedInterface
{
    use UserOwnedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Charge $charge = null;

    #[ORM\ManyToOne(inversedBy: 'claims', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reward $reward = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharge(): ?Charge
    {
        return $this->charge;
    }

    public function setCharge(Charge $charge): static
    {
        $this->charge = $charge;

        return $this;
    }

    public function getReward(): ?Reward
    {
        return $this->reward;
    }

    public function setReward(?Reward $reward): static
    {
        $this->reward = $reward;

        return $this;
    }
}
