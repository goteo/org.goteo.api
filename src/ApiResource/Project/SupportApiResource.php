<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Gateway\ChargeApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Money;
use App\Entity\Project\Support;
use App\Mapping\Transformer\SupportMoneyMapTransformer;
use App\State\ApiResourceStateProvider;
use App\State\Project\SupportStateProcessor;
use AutoMapper\Attribute\MapFrom;
use Symfony\Component\Validator\Constraints as Assert;

#[API\ApiResource(
    shortName: 'ProjectSupport',
    stateOptions: new Options(entityClass: Support::class),
    provider: ApiResourceStateProvider::class,
)]
#[API\GetCollection()]
#[API\Get()]
#[API\Patch(
    security: 'is_granted("SUPPORT_EDIT", previous_object)',
    processor: SupportStateProcessor::class
)]
class SupportApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The User who created the ProjectSupport record.\
     * \
     * When `anonymous` is *true* it will only be public to admins and the User.
     */
    #[API\ApiProperty(writable: false, security: 'is_granted("SUPPORT_VIEW", object)')]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public ?UserApiResource $owner;

    /**
     * The Project being targeted in the Charges.
     */
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public ProjectApiResource $project;

    /**
     * The Charges that were paid by the User.
     *
     * @var array<int, ChargeApiResource>
     */
    #[API\ApiProperty(writable: false)]
    public array $charges;

    /**
     * The total monetary value of the Charges paid by the User.
     */
    #[MapFrom(Support::class, transformer: SupportMoneyMapTransformer::class)]
    public Money $money;

    /**
     * User's will to have their support to the Project be shown publicly.
     */
    #[Assert\NotNull()]
    #[Assert\Type('bool')]
    public bool $anonymous = true;

    /**
     * A message of support from the User to the Project.
     */
    public ?string $message = null;
}
