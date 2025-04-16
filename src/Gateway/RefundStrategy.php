<?php

namespace App\Gateway;

enum RefundStrategy: string
{
    /**
     * The money will be kept by the platform and held for the User
     * to pay later without going again to an external gateway.
     */
    case ToWallet = 'to_wallet';

    /**
     * The gateway will emit a refund operation and
     * the money will return to the original payment method.
     */
    case ToGateway = 'to_gateway';
}
