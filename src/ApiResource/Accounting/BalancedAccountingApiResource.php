<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Metadata as API;
use App\Entity\Accounting\Accounting;
use App\Entity\Money;
use App\Mapping\Transformer\AccountingBalanceMapTransformer;
use AutoMapper\Attribute\MapFrom;

class BalancedAccountingApiResource extends AccountingApiResource
{
    /**
     * The money currently held by the Accounting.
     */
    #[MapFrom(Accounting::class, transformer: AccountingBalanceMapTransformer::class)]
    #[API\ApiProperty(writable: false, security: 'is_granted("ACCOUNTING_VIEW", object)')]
    public Money $balance;
}
