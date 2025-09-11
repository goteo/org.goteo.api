<?php

namespace App\Dto\Gateway;

use ApiPlatform\Metadata as API;
use App\Entity\Gateway\Checkout;
use App\Gateway\RefundStrategy;
use AutoMapper\Attribute\MapTo;

class CheckoutUpdationDto
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The strategy chosen by the User to decide where the money will go to
     * in the event that one Charge needs to be returned.
     */
    #[MapTo(Checkout::class, property: 'refundStrategy')]
    public RefundStrategy $refund;
}
