<?php

namespace App\Entity;

use App\Mapping\Provider\EntityMapProvider;
use App\Repository\CategoryRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category implements LocalizedInterface
{
    use LocalizedTrait;

    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Translatable()]
    private ?string $name = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
