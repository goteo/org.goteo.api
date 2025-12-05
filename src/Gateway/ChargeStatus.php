<?php

namespace App\Gateway;

enum ChargeStatus: string
{
    /**
     * Charge was generated but payment confirmation by the Gateway is pending.
     */
    case ToCharge = 'to_charge';

    /**
     * The Gateway confirmed the payment and the funds are available to the platform.
     */
    case InCharge = 'in_charge';

    /**
     * The charged money needs to be refunded with the Gateway.
     */
    case ToRefund = 'to_refund';

    /**
     * The charged money has been refunded with the Gateway.
     */
    case Refunded = 'refunded';

    /**
     * The charged money has been moved to the Wallet.
     */
    case Walleted = 'walleted';
}
