<?php

namespace App\Gateway;

enum CheckoutStatus: string
{
    case ToCharge = 'to_charge';

    case Charged = 'charged';
}
