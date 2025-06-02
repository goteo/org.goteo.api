<?php

namespace App\Entity\Project;

use App\Entity\Gateway\Charge;
use App\Entity\User\User;
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
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'supports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    /**
     * @var Collection<int, Charge>
     */
    #[ORM\JoinTable(name: 'project_support_charges')]
    #[ORM\JoinColumn(name: 'support_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'charge_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Charge::class)]
    private Collection $charges;

    #[ORM\Column]
    private ?bool $anonymous = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $message = null;

    public function __construct()
    {
        $this->charges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

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
     * @return Collection<int, Charge>
     */
    public function getCharges(): Collection
    {
        return $this->charges;
    }

    public function addCharge(Charge $charge): static
    {
        if (!$this->charges->contains($charge)) {
            $this->charges->add($charge);
        }

        return $this;
    }

    public function removeCharge(Charge $charge): static
    {
        $this->charges->removeElement($charge);

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
