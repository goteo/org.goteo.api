<?php

namespace App\Dto\Gateway;

use ApiPlatform\Metadata as API;
use App\Gateway\ChargeStatus;
use Symfony\Component\Validator\Constraints as Assert;

class ChargeUpdationDto
{
    /**
     * The unique identifier for the GatewayCharge.
     */
    #[API\ApiProperty(writable: false)]
    public int $id;

    /**
     * To ask for a refund, set the status `to_refund`.
     */
    #[Assert\Choice([ChargeStatus::ToRefund])]
    public ChargeStatus $status;
}
