<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\Money;
use App\Entity\Project\BudgetItem;
use App\Entity\Project\BudgetItemType;
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
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    public ProjectApiResource $project;

    /**
     * The type of need this item solves.
     */
    #[Assert\NotBlank()]
    public BudgetItemType $type;

    /**
     * List of the available content locales.
     *
     * @var array<string>
     */
    #[API\ApiProperty(writable: false)]
    public array $locales;

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
     * How much money it's needed for this item to be succesfully satisfied.
     */
    #[Assert\Valid()]
    public Money $minimum;

    /**
     * How much money would be ideal for this item to be fully satisfied.
     */
    #[Assert\Valid()]
    public Money $optimum;
}
