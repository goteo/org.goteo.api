<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Matchfunding\MatchCallApiResource;
use App\ApiResource\Project\ProjectApiResource;
use App\ApiResource\TipjarApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Accounting\Accounting;
use App\Entity\Matchfunding\MatchCall;
use App\Entity\Project\Project;
use App\Entity\Tipjar;
use App\Entity\User\User;
use App\State\Accounting\AccountingStateProcessor;
use App\State\Accounting\AccountingStateProvider;

/**
 * v4 features an advanced economy model under the hood.
 * Accountings are implemented as a common interface for issuing and receiving Transactions,
 * which allows different resources to have money-capabalities.
 * \
 * \
 * Many different actions can trigger changes in Accountings, such as GatewayCheckouts being successfully charged.
 */
#[API\ApiResource(
    shortName: 'Accounting',
    stateOptions: new Options(entityClass: Accounting::class),
    provider: AccountingStateProvider::class,
    processor: AccountingStateProcessor::class,
)]
#[API\GetCollection()]
#[API\Get()]
#[API\Patch(security: 'is_granted("ACCOUNTING_EDIT", object)')]
class AccountingApiResource
{
    public int $id;

    /**
     * The preferred currency for monetary operations.\
     * 3-letter ISO 4217 currency code.
     */
    public string $currency;

    #[API\ApiProperty(readable: false, writable: false)]
    public string $ownerClass;

    #[API\ApiProperty(readable: false, writable: false)]
    public ?UserApiResource $user = null;

    #[API\ApiProperty(readable: false, writable: false)]
    public ?ProjectApiResource $project = null;

    #[API\ApiProperty(readable: false, writable: false)]
    public ?TipjarApiResource $tipjar = null;

    #[API\ApiProperty(readable: false, writable: false)]
    public ?MatchCallApiResource $matchCall;

    /**
     * The resource owning this Accounting.
     *
     * @return UserApiResource|ProjectApiResource|TipjarApiResource
     */
    public function getOwner(): ?object
    {
        switch ($this->ownerClass) {
            case User::class:
                return $this->user;
            case Project::class:
                return $this->project;
            case Tipjar::class:
                return $this->tipjar;
            case MatchCall::class:
                return $this->matchCall;
        }

        return null;
    }
}
