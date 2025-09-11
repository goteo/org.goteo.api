<?php

namespace App\Entity\Matchfunding;

use App\Entity\Project\Project;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Matchfunding\MatchCallSubmissionRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\ORM\Mapping as ORM;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Entity(repositoryClass: MatchCallSubmissionRepository::class)]
class MatchCallSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MatchCall $call = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(enumType: MatchCallSubmissionStatus::class)]
    private ?MatchCallSubmissionStatus $status = MatchCallSubmissionStatus::DEFAULT;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCall(): ?MatchCall
    {
        return $this->call;
    }

    public function setCall(?MatchCall $call): static
    {
        $this->call = $call;

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

    public function getStatus(): ?MatchCallSubmissionStatus
    {
        return $this->status;
    }

    public function setStatus(MatchCallSubmissionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
