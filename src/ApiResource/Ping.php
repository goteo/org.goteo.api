<?php

namespace App\ApiResource;

use ApiPlatform\Metadata as API;
use App\State\PingStateProvider;

#[API\Get(
    uriTemplate: '/ping',
    provider: PingStateProvider::class,
)]
class Ping
{
    public function __construct(
        int $time,
        string $message,
    ) {
        $this->time = $time;
        $this->message = $message;
    }

    /**
     * The req-res time difference in milliseconds.
     */
    public int $time;

    public string $message;
}
