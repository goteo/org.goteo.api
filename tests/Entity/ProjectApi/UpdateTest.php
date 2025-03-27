<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\Client;

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
}
