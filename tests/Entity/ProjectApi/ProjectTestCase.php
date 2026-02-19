<?php

namespace App\Tests\Entity\ProjectApi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Territory;
use App\Factory\CategoryFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
use App\Tests\Traits\RequestingTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

use function Zenstruck\Foundry\Persistence\save;

abstract class ProjectTestCase extends ApiTestCase
{
    use ResetDatabase;
    use Factories;
    use RequestingTestTrait;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';
    protected const BASE_URI = '/v4/projects';

    public function setUp(): void
    {
        self::bootKernel();

        save(CategoryFactory::createOne(['id' => 'test']));
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
        $territory = new Territory('ES');

        $mergedAttributes = array_merge([
            'owner' => $owner,
            'territory' => $territory,
        ], $attributes);

        return ProjectFactory::createMany($count, $mergedAttributes);
    }

    // Auxiliary Tests

    protected function testOneNotFound(): void
    {
        $this->createTestProjectOptimized(1);

        $this->request($this->getMethod(), $this->getUri(999), [], Response::HTTP_NOT_FOUND);
    }

    protected function testForbidden(): void
    {
        $otherUser = $this->createTestUser('other_user', 'otheruser@example.com');
        $this->createTestProjectOptimized(1, ['owner' => $otherUser]);

        $this->request($this->getMethod(), $this->getUri(1));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
