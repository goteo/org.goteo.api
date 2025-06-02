<?php

namespace App\Gateway;

class Tracking
{
    /**
     * A descriptive title for the tracking number.
     */
    public string $title;

    /**
     * The tracking number given by the Gateway.
     */
    public string $value;
}
