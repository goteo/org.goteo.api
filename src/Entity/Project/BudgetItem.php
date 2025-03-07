<?php

namespace App\Entity\Project;

use App\Entity\Interface\LocalizedEntityInterface;
use App\Entity\Money;
use App\Entity\Trait\LocalizedEntityTrait;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Project\BudgetItemRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Table(name: 'project_budget_item')]
#[ORM\Entity(repositoryClass: BudgetItemRepository::class)]
class BudgetItem implements LocalizedEntityInterface
{
    use LocalizedEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'budgetItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(enumType: BudgetItemType::class)]
    private ?BudgetItemType $type = null;

    #[Gedmo\Translatable()]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Gedmo\Translatable()]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Embedded(class: Money::class)]
    private ?Money $minimum = null;

    #[ORM\Embedded(class: Money::class)]
    private ?Money $optimum = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?BudgetItemType
    {
        return $this->type;
    }

    public function setType(BudgetItemType $type): static
    {
        $this->type = $type;

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

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMinimum(): ?Money
    {
        return $this->minimum;
    }

    public function setMinimum(Money $minimum): static
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function getOptimum(): ?Money
    {
        return $this->optimum;
    }

    public function setOptimum(Money $optimum): static
    {
        $this->optimum = $optimum;

        return $this;
    }
}
