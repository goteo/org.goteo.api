<?php

namespace App\Tests\Entity\ProjectApi;

use Symfony\Component\HttpFoundation\Response;

class UpdateTest extends ProjectTestCase
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

        $this->request($this->getMethod(), $this->getUri(1), ['json' => $dataToModify]);
    }

    public function testUpdateUnauthorized(): void
    {
        $this->createTestProjectOptimized();

        static::createClient()->request($this->getMethod(), $this->getUri(1));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
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
            'categories' => ['invalid-category'],
        ];

        $this->request($this->getMethod(), $this->getUri(1), ['json' => $invalidInput]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testUpdateForbidden(): void
    {
        $this->testForbidden();
    }
}
