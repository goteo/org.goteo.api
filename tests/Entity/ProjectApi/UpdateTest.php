<?php

namespace App\Tests\Entity\ProjectApi;

use Symfony\Component\HttpFoundation\Response;

class UpdateTest extends BaseTest
{
    // Auxiliary functions

    protected function getMethod(): string
    {
        return 'PATCH';
    }

    // Runable Tests

    public function testUpdateSuccessful(): void
    {
        $this->createTestProjectOptimized();

        $dataToModify = [
            'title' => 'New project title',
            'description' => 'Updated project description',
        ];

        $this->testInsert($dataToModify, $this->getUri(1));
    }

    public function testUpdateUnauthorized(): void
    {
        $this->createTestProjectOptimized();

        static::createClient()->request($this->getMethod(), $this->getUri(1));

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testUpdateWithInvalidToken(): void
    {
        $this->testInvalidToken($this->getUri(1), 'application/merge-patch+json');
    }

    public function testUpdateNotFound(): void
    {
        $this->testOneNotFound();
    }

    public function testUpdateInvalidInput(): void
    {
        $this->createTestProjectOptimized();
        $invalidInput = [
            'title' => 'New project title',
            'category' => 'invalid-category',
        ];

        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            $this->getUri(1),
            [
                'headers' => $this->getHeaders($client),
                'json' => $invalidInput,
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testUpdateForbidden(): void
    {
        $this->testForbidden();
    }
}
