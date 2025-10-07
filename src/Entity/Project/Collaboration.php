<?php

namespace App\Entity\Project;

use App\ApiResource\TimestampedCreationApiResource;
use App\ApiResource\TimestampedUpdationApiResource;
use App\Entity\Trait\LocalizedEntityTrait;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Project\CollaborationRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Table(name: 'project_collaboration')]
#[ORM\Entity(repositoryClass: CollaborationRepository::class)]
class Collaboration
{
    use LocalizedEntityTrait;
    use TimestampedCreationApiResource;
    use TimestampedUpdationApiResource;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collaborations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Translatable()]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Gedmo\Translatable()]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isFulfilled = null;

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

    public function isFulfilled(): ?bool
    {
        return $this->isFulfilled;
    }

    public function setFulfilled(bool $isFulfilled): static
    {
        $this->isFulfilled = $isFulfilled;

        return $this;
    }
}
