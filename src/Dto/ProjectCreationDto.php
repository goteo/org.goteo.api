<?php

namespace App\Dto;

use App\ApiResource\Project\ProjectTerritoryApiResource;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectCategory;
use App\Entity\Project\ProjectDeadline;
use App\Mapping\Transformer\ProjectVideoMapTransformer;
use AutoMapper\Attribute\MapTo;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectCreationDto
{
    /**
     * Main headline for the Project.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Secondary headline for the Project.
     */
    #[Assert\NotBlank()]
    public string $subtitle;

    /**
     * One of the available categories.
     */
    #[Assert\NotBlank()]
    public ProjectCategory $category;

    /**
     * ISO 3166 data about the Project's territory of interest.
     */
    #[Assert\NotBlank()]
    #[Assert\Valid()]
    public ProjectTerritoryApiResource $territory;

    /**
     * Free-form rich text description for the Project.
     */
    #[Assert\NotBlank()]
    public string $description;

    /**
     * On `minimum`, Project will campaign until the minimum deadline.\
     * On `optimum`, Project will campaing until the minimum deadline,
     * and then until the optimum deadline if it did raise the minimum.
     */
    public ProjectDeadline $deadline = ProjectDeadline::Minimum;

    /**
     * A URL to a video showcasing the Project.
     */
    #[Assert\Url()]
    #[MapTo(target: Project::class, transformer: ProjectVideoMapTransformer::class)]
    public string $video;
}
