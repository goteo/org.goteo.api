<?php

namespace App\Entity\Project;

use App\Repository\Project\ReviewAreaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewAreaRepository::class)]
class ReviewArea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'areas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Review $review = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(enumType: ReviewAreaRisk::class)]
    private ?ReviewAreaRisk $risk = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): static
    {
        $this->review = $review;

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

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getRisk(): ?ReviewAreaRisk
    {
        return $this->risk;
    }

    public function setRisk(?ReviewAreaRisk $risk): static
    {
        $this->risk = $risk;

        return $this;
    }
}
