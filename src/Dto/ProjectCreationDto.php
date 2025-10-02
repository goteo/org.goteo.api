<?php

namespace App\Dto;

use ApiPlatform\Metadata as API;
use App\ApiResource\CategoryApiResource;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectCreationDto
{
    /**
     * Main headline for the Project. Must include at least one character between a-Z.
     */
    #[Assert\NotBlank()]
    #[Assert\Regex('/[a-zA-Z]{1,}/')]
    public string $title;

    /**
     * Secondary headline for the Project.
     */
    #[Assert\NotBlank()]
    public string $subtitle;

    /**
     * One of the available categories.
     *
     * @var CategoryApiResource[]
     */
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1, max: 2)]
    #[API\ApiProperty(writableLink: false)]
    public array $categories;
}
