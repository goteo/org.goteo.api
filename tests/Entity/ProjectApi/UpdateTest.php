<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response;

class UpdateTest extends BaseTest
{
    // Auxiliary functions

    protected function getMethod(): string
    {
        return 'PATCH';
    }

    protected function getHeaders(Client $client): array
    {
        return [
            'Authorization' => 'Bearer '.$this->getValidToken($client),
            'Content-Type' => 'application/merge-patch+json',
        ];
    }

    // Runable Tests

    public function testUpdateWithValidToken(): void
    {
        $this->prepareTestProject();

        $this->testInsert(['title' => 'Modified Title'], $this->getUri());
    }

    public function testUpdateUnauthorized(): void
    {
        $this->prepareTestProject();

        static::createClient()->request($this->getMethod(), $this->getUri());

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
