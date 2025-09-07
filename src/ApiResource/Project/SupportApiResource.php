<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\ApiResource\Accounting\TransactionApiResource;
use App\ApiResource\ApiMoney;
use App\Entity\Project\Support;
use App\State\ApiResourceStateProvider;
use App\State\MoneyTotalStateProvider;
use App\State\Project\SupportStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProjectSupports gather Transactions going from one same origin to one same Project.\
 * \
 * MatchCalls specially might make several different Transactions to the same Project,
 * but their ProjectSupport remains the same just with updated money.
 */
#[API\ApiResource(
    shortName: 'ProjectSupport',
    stateOptions: new Options(entityClass: Support::class),
    provider: ApiResourceStateProvider::class,
)]
#[API\GetCollection()]
#[API\GetCollection(
    uriTemplate: '/project_supports/money_total',
    provider: MoneyTotalStateProvider::class,
    paginationEnabled: false,
)]
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
     * The Project being supported.
     */
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public ProjectApiResource $project;

    /**
     * The Accounting of origin for the Transactions under this ProjectSupport record.\
     * \
     * When `anonymous` is *true* it will only be public to admins and the User.
     */
    #[API\ApiProperty(writable: false, security: 'is_granted("SUPPORT_VIEW", object)')]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public ?AccountingApiResource $origin;

    /**
     * The Transactions that were issued to the Project by the origin.
     *
     * @var array<int, TransactionApiResource>
     */
    #[API\ApiProperty(writable: false)]
    public array $transactions;

    /**
     * The total monetary value of the Transactions going to the Project.
     */
    public ApiMoney $money;

    /**
     * User's will to have their support to the Project be shown publicly.
     */
    #[Assert\NotNull()]
    #[Assert\Type('bool')]
    #[API\ApiFilter(BooleanFilter::class)]
    public bool $anonymous = true;

    /**
     * A message of support from the User to the Project.
     */
    public ?string $message = null;
}
