<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }
}
