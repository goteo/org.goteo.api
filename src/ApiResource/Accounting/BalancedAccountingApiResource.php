<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Metadata as API;
use App\ApiResource\Matchfunding\MatchCallApiResource;
use App\ApiResource\Project\ProjectApiResource;
use App\ApiResource\TipjarApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Accounting\Accounting;
use App\Entity\Matchfunding\MatchCall;
use App\Entity\Money;
use App\Entity\Project\Project;
use App\Entity\Tipjar;
use App\Entity\User\User;
use App\Mapping\Transformer\AccountingBalanceMapTransformer;
use AutoMapper\Attribute\MapFrom;
use Symfony\Component\Serializer\Attribute\Ignore;

class BalancedAccountingApiResource
{
    public int $id;

    /**
     * The preferred currency for monetary operations.\
     * 3-letter ISO 4217 currency code.
     */
    public string $currency;

    /**
     * The money currently held by the Accounting.
     */
    #[MapFrom(Accounting::class, transformer: AccountingBalanceMapTransformer::class)]
    #[API\ApiProperty(writable: false, security: 'is_granted("ACCOUNTING_VIEW", object)')]
    public Money $balance;

    #[Ignore]
    #[API\ApiProperty(readable: false, writable: false)]
    public string $ownerClass;

    #[Ignore]
    #[API\ApiProperty(readable: false, writable: false)]
    public ?UserApiResource $user = null;

    #[Ignore]
    #[API\ApiProperty(readable: false, writable: false)]
    public ?ProjectApiResource $project = null;

    #[Ignore]
    #[API\ApiProperty(readable: false, writable: false)]
    public ?TipjarApiResource $tipjar = null;

    #[Ignore]
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
