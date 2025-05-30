<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\LocalizedApiResourceTrait;
use App\Entity\Money;
use App\Entity\Project\Reward;
use App\State\ApiResourceStateProvider;
use App\State\Project\RewardStateProcessor;
use AutoMapper\Attribute\MapTo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A ProjectReward is something the Project owner wishes to give in exchange for contributions to their Project.
 */
#[API\ApiResource(
    shortName: 'ProjectReward',
    stateOptions: new Options(entityClass: Reward::class),
    provider: ApiResourceStateProvider::class,
    processor: RewardStateProcessor::class
)]
class RewardApiResource
{
    use LocalizedApiResourceTrait;

    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The project which gives this reward.
     */
    #[Assert\NotBlank()]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public ProjectApiResource $project;

    /**
     * A short, descriptive title for this reward.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Information about this reward. More detailed than the title.
     */
    #[Assert\NotBlank()]
    #[MapTo(if: 'source.description != null')]
    public ?string $description = null;

    /**
     * The minimal monetary sum to be able to claim this reward.
     */
    #[Assert\NotBlank()]
    public Money $money;

    /**
     * Rewards might be finite, i.e: has a limited amount of existing unitsTotal.
     */
    #[Assert\NotNull()]
    #[Assert\Type('bool')]
    public bool $hasUnits;

    /**
     * For finite rewards, the total amount of existing unitsTotal.\
     * Required if `hasUnits`.
     */
    #[Assert\When(
        'this.hasUnits == true',
        constraints: [new Assert\Positive()]
    )]
    public int $unitsTotal = 0;

    /**
     * For finite rewards, the currently available amount of unitsTotal that can be claimed.
     */
    #[API\ApiProperty(writable: false)]
    public int $unitsAvailable = 0;
}
