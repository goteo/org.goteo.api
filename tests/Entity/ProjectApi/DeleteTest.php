<?php

namespace App\Tests\Entity\ProjectApi;

use Symfony\Component\HttpFoundation\Response;

class DeleteTest extends BaseTestCase
{
    protected function getMethod(): string
    {
        return 'DELETE';
    }

    // Runable Tests

    public function testDeleteWithValidToken(): void
    {
        $this->createTestProjectOptimized();

        $client = static::createClient();
        $client->request('DELETE', $this->getUri(1), $this->getRequestOptions($client));

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteUnauthorized()
    {
        $this->createTestProjectOptimized();

        static::createClient()->request('DELETE', $this->getUri(1));

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testDeleteWithInvalidToken(): void
    {
        $this->testWithInvalidToken($this->getUri(1));
    }

    public function testDeleteNotFound(): void
    {
        $this->testOneNotFound();
    }

    public function testDeleteForbidden(): void
    {
        $this->testForbidden();
    }
}
