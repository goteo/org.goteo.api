<?php

namespace App\Dto;

use ApiPlatform\Metadata as API;
use App\ApiResource\Project\ProjectTerritoryApiResource;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectCategory;
use App\Entity\Project\ProjectDeadline;
use App\Entity\Project\ProjectStatus;
use App\Mapping\Transformer\ProjectVideoMapTransformer;
use AutoMapper\Attribute\MapTo;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectUpdateDto
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * Main headline for the Project.
     */
    public string $title;

    /**
     * Secondary headline for the Project.
     */
    public string $subtitle;

    /**
     * One of the available categories.
     */
    public ProjectCategory $category;

    /**
     * ISO 3166 data about the Project's territory of interest.
     */
    #[Assert\Valid()]
    public ProjectTerritoryApiResource $territory;

    /**
     * Free-form rich text description for the Project.
     */
    public string $description;

    /**
     * On `minimum`, Project will campaign until the minimum deadline.\
     * On `optimum`, Project will campaing until the minimum deadline,
     * and then until the optimum deadline if it did raise the minimum.
     */
    public ProjectDeadline $deadline;

    /**
     * A URL to a video showcasing the Project.
     */
    #[Assert\Url()]
    #[MapTo(target: Project::class, transformer: ProjectVideoMapTransformer::class)]
    public string $video;

    /**
     * The status of a Project represents how far it is in it's life-cycle.
     */
    public ProjectStatus $status;
}
