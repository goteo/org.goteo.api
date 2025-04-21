<?php

namespace App\Dto\Gateway;

use App\Gateway\ChargeStatus;
use App\Gateway\ChargeType;

class ChargeUpdationDto
{
    public int $id;
    public ChargeType $type;

    public string $title;

    public string $description;

    public ChargeStatus $status;
}
