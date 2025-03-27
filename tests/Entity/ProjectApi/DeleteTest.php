<?php

namespace App\Tests\Entity\ProjectApi;

use Symfony\Component\HttpFoundation\Response;

class DeleteTest extends BaseTest
{
    protected function getMethod(): string
    {
        return 'DELETE';
    }

    // Runable Tests

    public function testDeleteWithValidToken(): void
    {
        $this->prepareTestProject();

        $client = static::createClient();
        $client->request('DELETE', $this->getUri(1), ['headers' => $this->getHeaders($client)]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteUnauthorized()
    {
        $this->prepareTestProject();

        static::createClient()->request('DELETE', $this->getUri(1));

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testDeleteWithInvalidToken(): void
    {
        static::createClient()->request(
            'DELETE',
            $this->getUri(1),
            ['headers' => ['Authorization' => 'Bearer 123']]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testDeleteNotFound(): void
    {
        $this->testOneNotFound();
    }

    public function testDeleteForbidden(): void
    {
        $otherUser = $this->createTestUser()
            ->setHandle('other_user')->setEmail('otheruser@example.com');
        $otherProject = $this->createTestProject()->setOwner($otherUser);
        $this->entityManager->persist($otherProject);
        $this->prepareTestUser();

        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            $this->getUri(1),
            ['headers' => $this->getHeaders($client)]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
