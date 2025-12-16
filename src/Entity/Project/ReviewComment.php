<?php

namespace App\Entity\Project;

use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\TimestampedUpdationEntity;
use App\Entity\User\User;
use App\Repository\Project\ReviewCommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewCommentRepository::class)]
class ReviewComment
{
    use TimestampedCreationEntity;
    use TimestampedUpdationEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $author = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReviewArea $area = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getArea(): ?ReviewArea
    {
        return $this->area;
    }

    public function setArea(?ReviewArea $area): static
    {
        $this->area = $area;

        return $this;
    }
}
