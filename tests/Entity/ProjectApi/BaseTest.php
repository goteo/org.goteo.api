<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Project\ProjectTerritory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
use App\Tests\Traits\TestHelperTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class BaseTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;
    use TestHelperTrait;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';
    protected const BASE_URI = '/v4/projects';

    public function setUp(): void
    {
        self::bootKernel();
    }

    // Auxiliary Methods

    /**
     * Defines the HTTP method that will be used in the requests made by the tests.
     *
     * @return string The HTTP method to be used for the request
     *                (e.g., 'GET', 'POST', 'PUT', 'DELETE').
     */
    abstract protected function getMethod(): string;

    protected function getUri(?int $id = null): string
    {
        $param = $id == null ? '' : "/$id";

        return self::BASE_URI.$param;
    }

    protected function getHeaders(Client $client): array
    {
        return $this->getAuthHeaders(
            $client,
            self::USER_EMAIL,
            self::USER_PASSWORD,
            $this->getMethod()
        );
    }

    protected function getRequestOptions(Client $client, array $data = []): array
    {
        $options = ['headers' => $this->getHeaders($client)];

        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $options;
    }

    protected function createTestUser(
        string $handle = 'test_user',
        string $email = self::USER_EMAIL,
    ) {
        return UserFactory::createOne([
            'handle' => $handle,
            'email' => $email,
            'password' => self::USER_PASSWORD,
        ]);
    }

    protected function createTestProjectOptimized(int $count = 1, array $attributes = []): array
    {
        $owner = $this->createTestUser();
        $territory = new ProjectTerritory('ES');

        $mergedAttributes = array_merge([
            'owner' => $owner,
            'territory' => $territory,
        ], $attributes);

        return ProjectFactory::createMany($count, $mergedAttributes);
    }

    // Auxiliary Tests

    protected function testRequestHelper(
        array $data = [],
        string $uri = self::BASE_URI,
        int $expectedCode = Response::HTTP_OK,
    ): void {
        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            $uri,
            $this->getRequestOptions($client, $data)
        );

        $this->assertResponseStatusCodeSame($expectedCode);
    }

    protected function testOneNotFound(): void
    {
        $this->createTestProjectOptimized(1);

        $this->testRequestHelper([], $this->getUri(999), Response::HTTP_NOT_FOUND);
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

    protected function testForbidden(): void
    {
        $otherUser = $this->createTestUser('other_user', 'otheruser@example.com');
        $this->createTestProjectOptimized(1, ['owner' => $otherUser]);

        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            $this->getUri(1),
            $this->getRequestOptions($client)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
