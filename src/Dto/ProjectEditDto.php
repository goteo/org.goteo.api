<?php

namespace App\Dto;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata as API;
use App\ApiResource\Project\ProjectTerritoryApiResource;
use App\Entity\Project\Category;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\Mapping\Transformer\ProjectVideoMapTransformer;
use AutoMapper\Attribute\MapTo;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectEditDto
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * Main headline for the Project.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Secondary headline for the Project.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank()]
    public string $subtitle;

    /**
     * One of the available categories.
     */
    #[Assert\NotBlank()]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public Category $category;

    /**
     * ISO 3166 data about the Project's territory of interest.
     */
    #[Assert\NotBlank()]
    #[Assert\Valid()]
    public ProjectTerritoryApiResource $territory;

    /**
     * Free-form rich text description for the Project.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank()]
    public string $description;

    /**
     * A URL to a video showcasing the Project.
     */
    #[Assert\Url()]
    #[MapTo(target: Project::class, transformer: ProjectVideoMapTransformer::class)]
    public string $video;

    /**
     * The status of a Project represents how far it is in it's life-cycle.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("PROJECT_EDIT", previous_object)')]
    public ProjectStatus $status = ProjectStatus::InEditing;
}
