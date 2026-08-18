<?php

namespace App\Dto;

use ApiPlatform\Metadata as API;
use App\ApiResource\CategoryApiResource;
use App\Entity\Project\ProjectCalendar;
use App\Entity\Project\ProjectStatus;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectCreationDto
{
    use CategoryInputDtoTrait;

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
     * URL to an image resource to be displayed as header.
     */
    #[Assert\Url()]
    public string $cover;

    /**
     * List of Categories.
     *
     * @var CategoryApiResource[]
     */
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1, max: 2)]
    #[API\ApiProperty(writableLink: false, openapiContext: self::CATEGORIES_OPENAPI_CONTEXT)]
    public array $categories;

    /**
     * Deadlines and important Project dates.
     */
    #[Assert\Valid()]
    public ProjectCalendar $calendar;

    #[API\ApiProperty(writable: false)]
    public ProjectStatus $status = ProjectStatus::InDraft;
}
