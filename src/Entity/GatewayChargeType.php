<?php

namespace App\Entity;

enum GatewayChargeType: string
{
    /**
     * A one-time charge to the money payer.
     */
    case Single = 'single';

    /**
     * A recurring charge to the money payer.
     */
    case Recurring = 'recurring';
}
