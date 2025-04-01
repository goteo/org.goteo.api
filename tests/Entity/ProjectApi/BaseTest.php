<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Project\Category;
use App\Entity\Project\ProjectDeadline;
use App\Entity\Project\ProjectStatus;
use App\Entity\Project\ProjectTerritory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
use App\Tests\Traits\TestHelperTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class BaseTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;
    use TestHelperTrait;

    protected EntityManagerInterface $entityManager;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';
    protected const BASE_URI = '/v4/projects';

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    // Auxiliary Methods

    /**
     * Defines the HTTP method that will be used in the requests made by the tests.
     *
     * @return string The HTTP method to be used for the request
     *                (e.g., 'GET', 'POST', 'PUT', 'DELETE').
     */
    abstract protected function getMethod(): string;

    protected function getUri(int $id = -1): string
    {
        $param = $id < 0 ? '' : "/$id";

        return self::BASE_URI.$param;
    }

    protected function getRequestOptions(Client $client)
    {
        return ['headers' => $this->getHeaders($client)];
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

    protected function getExampleProjectData(): array
    {
        return [
            'id' => 1,
            'title' => 'Test Project',
            'subtitle' => 'Test Project Subtitle',
            'category' => Category::LibreSoftware,
            'territory' => ['country' => 'ES'],
            'description' => 'Test Project Description',
            'deadline' => ProjectDeadline::Minimum,
            'status' => ProjectStatus::InEditing,
        ];
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

    protected function getHeaders(Client $client): array
    {
        $method = $this->getMethod();

        return $this->getAuthHeaders($client, self::USER_EMAIL, self::USER_PASSWORD, $method);
    }

    // Auxiliary Tests

    protected function testOneNotFound(): void
    {
        $this->createTestProjectOptimized(1);

        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            $this->getUri(999),
            $this->getRequestOptions($client)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    protected function testInsert(
        array $data,
        string $uri = self::BASE_URI,
        int $expectedCode = Response::HTTP_OK,
    ): void {
        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            $uri,
            ['headers' => $this->getHeaders($client), 'json' => $data]
        );

        $this->assertResponseStatusCodeSame($expectedCode);
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
