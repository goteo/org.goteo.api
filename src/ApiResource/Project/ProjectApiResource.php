<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\Entity\Project\ProjectVideo;
use App\Mapping\Transformer\BudgetMapTransformer;
use App\Mapping\Transformer\ProjectVideoMapTransformer;
use App\State\ApiResourceStateProvider;
use App\State\Project\ProjectStateProcessor;
use AutoMapper\Attribute\MapFrom;
use AutoMapper\Attribute\MapTo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Projects describe a User-owned, community-led event that is to be discovered, developed and funded by the community.
 */
#[API\ApiResource(
    shortName: 'Project',
    stateOptions: new Options(entityClass: Project::class),
    provider: ApiResourceStateProvider::class,
    processor: ProjectStateProcessor::class
)]
#[API\GetCollection()]
#[API\Post(security: 'is_granted("ROLE_USER")')]
#[API\Get()]
#[API\Patch(security: 'is_granted("PROJECT_EDIT", object)')]
#[API\Delete(security: 'is_granted("PROJECT_EDIT", object)')]
class ProjectApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The Accounting holding the funds raised by this Project.
     */
    #[API\ApiProperty(writable: false)]
    public AccountingApiResource $accounting;

    /**
     * The User who owns this Project.
     */
    #[API\ApiProperty(writable: false)]
    public UserApiResource $owner;

    /**
     * List of the available content locales.
     *
     * @var array<string>
     */
    #[API\ApiProperty(writable: false)]
    public array $locales;

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
    #[API\ApiProperty(readable: false)]
    #[MapTo(target: Project::class, transformer: ProjectVideoMapTransformer::class)]
    public string $video;

    /**
     * Extracted embedding data from the Project's video.
     */
    #[API\ApiProperty(writable: false)]
    #[MapFrom(source: Project::class, property: 'video')]
    public ProjectVideo $videoEmbed;

    /**
     * The status of a Project represents how far it is in it's life-cycle.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("PROJECT_EDIT")')]
    public ProjectStatus $status = ProjectStatus::InEditing;

    /**
     * List of the ProjectRewards this Project offers.
     *
     * @var array<int, RewardApiResource>
     */
    #[API\ApiProperty(writable: false)]
    public array $rewards;

    /**
     * A detailed breakdown of the budget for this Project, as described by the associated BudgetItems.
     */
    #[API\ApiProperty(writable: false)]
    #[MapFrom(source: Project::class, transformer: BudgetMapTransformer::class)]
    public Budget $budget;

    /**
     * A list of the BudgetItems composing the budget of this Project.
     *
     * @var array<int, BudgetItemApiResource>
     */
    #[API\ApiProperty(writable: false)]
    public array $budgetItems;
}
