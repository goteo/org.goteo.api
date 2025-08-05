<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Metadata as API;
use App\Entity\Accounting\Accounting;
use App\Entity\Money;
use App\State\Accounting\AccountingBalanceStateProvider;

/**
 * The AccountingBalance represents how much Money is currently held under one Accounting.\
 * \
 * This is calculated from incoming vs outgoing Transactions in the Accounting's history.
 */
#[API\ApiResource(
    shortName: 'AccountingBalance',
)]
#[API\Get(
    provider: AccountingBalanceStateProvider::class,
    uriTemplate: '/accountings/{id}/balance',
    uriVariables: [
        'id' => new API\Link(
            fromClass: AccountingApiResource::class,
            fromProperty: 'id',
            description: 'Accounting identifier'
        ),
    ]
)]
class AccountingBalance
{
    #[API\ApiProperty(identifier: true)]
    public AccountingApiResource $accounting;

    /**
     * The money currently held by the Accounting.
     */
    #[API\ApiProperty(security: 'is_granted("ACCOUNTING_VIEW", object.accounting)')]
    public Money $balance;
}
