<?php

namespace App\Tests\Traits;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response;

trait TestHelperTrait
{
    protected function getValidToken(Client $client, string $email, string $password): string
    {
        $client->request(
            'POST',
            '/v4/user_tokens',
            [
                'json' => [
                    'identifier' => $email,
                    'password' => $password,
                ],
            ]
        );

        return json_decode($client->getResponse()->getContent(), true)['token'];
    }

    protected function getAuthHeaders(
        Client $client,
        string $email,
        string $password,
        string $method = 'GET',
    ): array {
        $contentType = $method == 'PATCH' ? 'application/merge-patch+json' : 'application/json';

        return [
            'Authorization' => 'Bearer '.$this->getValidToken($client, $email, $password),
            'Content-Type' => $contentType,
        ];
    }

    protected function testInvalidToken(
        string $uri,
        string $contentType = 'application/json',
        int $expectedToken = Response::HTTP_UNAUTHORIZED,
    ): void {
        static::createClient()->request(
            $this->getMethod(),
            $uri,
            ['headers' => [
                'Authorization' => 'Bearer invalid_token',
                'Content-Type' => $contentType,
            ]]
        );

        $this->assertResponseStatusCodeSame($expectedToken);
    }
}
