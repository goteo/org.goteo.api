<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\LocalizedApiResourceTrait;
use App\ApiResource\Money;
use App\Entity\Project\BudgetItem;
use App\Entity\Project\BudgetItemType;
use App\Entity\Project\ProjectDeadline;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Project's budget is composed via BudgetItem records.\
 * \
 * Each BudgetItem describes one specific monetary necessity. The total budget of a Project is then calculated from the related BudgetItems.
 */
#[API\ApiResource(
    shortName: 'ProjectBudgetItem',
    stateOptions: new Options(entityClass: BudgetItem::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
class BudgetItemApiResource
{
    use LocalizedApiResourceTrait;

    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public ProjectApiResource $project;

    /**
     * The type of need this item solves.
     */
    #[Assert\NotBlank()]
    public BudgetItemType $type;

    /**
     * A short, descriptive string for the item.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Detailed information about the item.
     */
    #[Assert\NotBlank()]
    public string $description;

    /**
     * The amount of money required for this item.
     */
    #[Assert\NotBlank()]
    #[Assert\Valid()]
    public Money $money;

    /**
     * Defines the budget category for this item within the project.
     * 
     * This field specifies whether the budget item belongs to the minimum or optimum budget:
     */
    #[Assert\Type(
        type: ProjectDeadline::class,
        message: \sprintf("The category must be one of these values: %s.", \join(", ", array_map(fn($case) => $case->name, ProjectDeadline::cases())))
        )]
    private ?ProjectDeadline $category = null;
}
