<?php

namespace App\Tests\Traits;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait RequestingTestTrait
{
    protected function request(
        string $method,
        string $uri,
        array $options = [],
        ?int $expectedCode = null,
    ): ResponseInterface {
        /** @var Client */
        $client = static::createClient();
        $response = $client->request($method, $uri, [
            'headers' => [
                ...$this->getAuthHeaders(['email']),
                'Content-Type' => match ($method) {
                    'PATCH' => 'application/merge-patch+json',
                    default => 'application/json',
                },
            ],
            ...$options,
        ]);

        if ($expectedCode !== null) {
            $this->assertResponseStatusCodeSame($expectedCode);
        }

        return $response;
    }

    protected function getAuthHeaders(array $scopes = []): array
    {
        return [
            'X-Test-Scopes' => \join(' ', $scopes),
        ];
    }
}
