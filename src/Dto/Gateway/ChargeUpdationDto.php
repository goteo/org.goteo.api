<?php

namespace App\Dto\Gateway;

use App\Gateway\ChargeStatus;
use App\Gateway\ChargeType;

class ChargeUpdationDto
{
    /**
     * The unique identifier for the charge.
     */
    public int $id;

    /**
     * The type of the charge.
     */
    public ChargeType $type;

    /**
     * The title or name of the charge.
     */
    public string $title;

    /**
     * A detailed description of the charge.
     */
    public string $description;

    /**
     * The current payment status of the charge.
     */
    public ChargeStatus $status;
}
