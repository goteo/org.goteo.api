<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Ping;

class PingStateProvider implements ProviderInterface
{
    private const CACHE_THANKS = 'Thank you. You just warmed-up cache for everyone else. Probably.';

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $time = $this->getTime($context['request']);
        $message = $this->getMessage($time);

        return new Ping($time, $message);
    }

    private function getTime($request): int
    {
        return \floor((\microtime(true) - $request->server->get('REQUEST_TIME_FLOAT')) * 1000);
    }

    private function getMessage(int $time): string
    {
        if ($time > 3000) {
            return self::CACHE_THANKS;
        }

        return 'pong';
    }
}
