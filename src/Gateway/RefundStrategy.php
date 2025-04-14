<?php

namespace App\Gateway;

enum RefundStrategy: string
{
    case ToPaymentMethod = 'to_payment_method';
    case ToWallet = 'to_wallet';
}
