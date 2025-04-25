<?php

namespace App\ApiResource\Gateway;

use ApiPlatform\Doctrine\Orm\Filter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\Dto\Gateway\ChargeUpdationDto;
use App\Entity\Gateway\Charge;
use App\Entity\Money;
use App\Gateway\ChargeStatus;
use App\Gateway\ChargeType;
use App\State\ApiResourceStateProvider;
use App\State\Gateway\ChargeStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Charge represents a payment item to be included in a Checkout for payment at a Gateway.
 */
#[API\ApiResource(
    shortName: 'GatewayCharge',
    stateOptions: new Options(entityClass: Charge::class),
    provider: ApiResourceStateProvider::class
)]
#[API\Get()]
#[API\GetCollection(security: "is_granted('IS_AUTHENTICATED_FULLY')")]
#[API\Patch(
    input: ChargeUpdationDto::class,
    processor: ChargeStateProcessor::class,
)]
class ChargeApiResource
{
    #[API\ApiProperty(writable: false, identifier: true)]
    public ?int $id = null;

    /**
     * The Checkout to which this Charge item belongs to.
     */
    #[API\ApiProperty(writable: false)]
    public CheckoutApiResource $checkout;

    /**
     * How this item should be processed by the Gateway.\
     * \
     * `single` is for one time payments.\
     * `recurring` is for payments repeated over time.
     */
    #[Assert\NotBlank()]
    public ChargeType $type = ChargeType::Single;

    /**
     * A short, descriptive string for this charge item.\
     * May be displayed to the payer.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Detailed information about the charge item.\
     * May be displayed to the payer.
     */
    public ?string $description = null;

    /**
     * The Accounting receiving the money after a successful payment.
     */
    #[Assert\NotBlank()]
    public AccountingApiResource $target;

    /**
     * The money to-be-paid for this item at the Gateway.
     *
     * It is money before fees and taxes, not accountable.
     */
    #[Assert\NotBlank()]
    #[API\ApiFilter(Filter\RangeFilter::class, properties: ['money.amount'])]
    #[API\ApiFilter(Filter\SearchFilter::class, properties: ['money.amount' => 'exact'])]
    public Money $money;

    /**
     * The status of the charge item with the Gateway.
     */
    #[Assert\NotBlank()]
    #[API\ApiFilter(Filter\SearchFilter::class, properties: ['status' => 'exact'])]
    public ChargeStatus $status = ChargeStatus::Pending;
}
