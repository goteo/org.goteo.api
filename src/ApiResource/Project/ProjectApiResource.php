<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\ApiResource\LocalizedApiResourceTrait;
use App\ApiResource\Matchfunding\MatchCallSubmissionApiResource;
use App\ApiResource\User\UserApiResource;
use App\Dto\ProjectCreationDto;
use App\Dto\ProjectUpdationDto;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectCalendar;
use App\Entity\Project\ProjectDeadline;
use App\Entity\Project\ProjectStatus;
use App\Entity\Project\ProjectVideo;
use App\Entity\Territory;
use App\Mapping\Transformer\BudgetMapTransformer;
use App\State\ApiResourceStateProvider;
use App\State\Project\ProjectStateProcessor;
use App\State\Project\ProjectStateProvider;
use AutoMapper\Attribute\MapFrom;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Projects describe a User-owned, community-led event that is to be discovered, developed and funded by the community.
 */
#[API\ApiResource(
    shortName: 'Project',
    stateOptions: new Options(entityClass: Project::class),
    provider: ApiResourceStateProvider::class,
)]
#[API\GetCollection()]
#[API\Post(
    input: ProjectCreationDto::class,
    processor: ProjectStateProcessor::class,
    security: 'is_granted("ROLE_USER")',
)]
#[API\Get(
    provider: ProjectStateProvider::class,
    uriTemplate: '/projects/{idOrSlug}',
    uriVariables: [
        'idOrSlug' => new API\Link(
            description: 'Project identifier or slug',
        ),
    ]
)]
#[API\Patch(
    input: ProjectUpdationDto::class,
    processor: ProjectStateProcessor::class,
    security: 'is_granted("PROJECT_EDIT", previous_object)',
)]
#[API\Delete(security: 'is_granted("PROJECT_EDIT", previous_object)')]
class ProjectApiResource
{
    use LocalizedApiResourceTrait;

    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * A unique, non white space, string identifier for this Project.
     */
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public string $slug;

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
     * On `minimum`, Project will campaign until the minimum deadline.\
     * On `optimum`, Project will campaing until the minimum deadline,
     * and then until the optimum deadline if it did raise the minimum.
     */
    #[API\ApiProperty(writable: false)]
    public ProjectDeadline $deadline;

    /**
     * Deadlines and important Project dates.
     */
    #[API\ApiProperty(writable: false)]
    public ProjectCalendar $calendar;

    /**
     * A list of the available categories most relevant to this Project.
     */
    #[Assert\NotBlank()]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public array $categories;

    /**
     * ISO 3166 data about the Project's territory of interest.
     */
    #[Assert\NotBlank()]
    #[Assert\Valid()]
    public Territory $territory;

    /**
     * Free-form rich text description for the Project.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank()]
    public string $description;

    /**
     * Extracted embedding data from the Project's video.
     */
    #[API\ApiProperty(writable: false)]
    #[MapFrom(source: Project::class, property: 'video')]
    public ProjectVideo $video;

    /**
     * The status of a Project represents how far it is in it's life-cycle.
     */
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("PROJECT_EDIT", previous_object)')]
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

    /**
     * A list of the ProjectUpdates this Project has.
     *
     * @var array<int, UpdateApiResource>
     */
    #[API\ApiProperty(writable: false)]
    public array $updates;

    /**
     * @var array<int, MatchCallSubmissionApiResource>
     */
    #[API\ApiProperty(writable: false)]
    public array $matchCallSubmissions;
}
