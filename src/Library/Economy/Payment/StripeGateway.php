<?php

namespace App\Library\Economy\Payment;

class StripeGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'stripe';
    }
}
