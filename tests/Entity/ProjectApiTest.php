<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Project\Category;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectDeadline;
use App\Entity\Project\ProjectStatus;
use App\Entity\Project\ProjectTerritory;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProjectApiTest extends ApiTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;
    private User $owner;

    private const TEST_USER_EMAIL = 'testuser@example.com';
    private const TEST_USER_PASSWORD = 'projectapitestuserpassword';

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->owner = $this->createTestUser();
    }

    private function createTestUser(): User
    {
        $user = new User();
        $user->setHandle('test_user');
        $user->setEmail(self::TEST_USER_EMAIL);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        $user->setPassword($passwordHasher->hashPassword($user, self::TEST_USER_PASSWORD));

        return $user;
    }

    private function getValidToken(Client $client): mixed
    {
        $this->entityManager->persist($this->owner);
        $this->entityManager->flush();

        $response = $client->request(
            'POST',
            '/v4/user_tokens',
            ['json' => [
                'identifier' => self::TEST_USER_EMAIL,
                'password' => self::TEST_USER_PASSWORD,
            ]]
        );

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);

        return $data['token'];
    }

    private function createTestProject(
        string $title = 'Test Project',
        string $subtitle = 'Test Project Subtitle',
        string $description = 'Test Project Description',
    ): Project {
        $project = new Project();
        $project->setTitle($title);
        $project->setSubtitle($subtitle);
        $project->setDeadline(ProjectDeadline::Minimum);
        $project->setCategory(Category::LibreSoftware);
        $project->setDescription($description);
        $project->setTerritory(new ProjectTerritory('ES'));
        $project->setOwner($this->owner);
        $project->setStatus(ProjectStatus::InEditing);

        return $project;
    }

    // TESTS

    public function testGetCollectionWithInvalidToken()
    {
        static::createClient()->request(
            'GET',
            '/v4/projects',
            ['headers' => ['Authorization' => 'Bearer invalid_token']]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetCollectionWithValidToken()
    {
        $client = static::createClient();
        $token = $this->getValidToken($client);

        // Use the token on request
        $client->request(
            'GET',
            '/v4/projects',
            ['headers' => ['Authorization' => "Bearer $token"]]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@type' => 'Collection']);
    }

    public function testGetCollectionWithoutToken()
    {
        static::createClient()->request('GET', '/v4/projects');

        // Decide if you return a 200 or 401
        $this->assertResponseIsSuccessful(); // 200
        // $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED); // 401
    }

    public function testGetCollection(): void
    {
        $client = static::createClient();

        $token = $this->getValidToken($client);
        $headers = ['headers' => ['Authorization' => "Bearer $token"]];

        $client->request('GET', '/v4/projects', $headers);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/v4/projects']);
        $this->assertJsonContains(['@type' => 'Collection']);
        $this->assertJsonContains(['totalItems' => 0]);
        $this->assertJsonContains(['member' => []]);

        $project = $this->createTestProject();

        $this->entityManager->persist($this->owner);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $client->request('GET', '/v4/projects', $headers);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertJsonContains(['member' => [
            [
                'title' => 'Test Project',
                'status' => ProjectStatus::InEditing->value,
                'rewards' => [],
            ],
        ]]);
    }

    public function testGetCollectionWithPagination()
    {
        $client = static::createClient();
        $token = $this->getValidToken($client);
        $headers = ['headers' => ['Authorization' => "Bearer $token"]];

        // Create multiple projects
        $this->entityManager->persist($this->owner);
        $pageSize = 30;
        $page = 2;
        $numberOfProjects = $pageSize * $page - $pageSize / 2;
        for ($i = 1; $i <= $numberOfProjects; ++$i) {
            $project = $this->createTestProject("Test Project $i", "Subtitle $i", "Description $i");

            $this->entityManager->persist($project);
        }

        $this->entityManager->flush();

        // Request the project page
        $response = $client->request('GET', "/v4/projects?page=$page", $headers);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@type' => 'Collection']);
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
        $memberCount = count($data['member']);
        $this->assertGreaterThan(0, $memberCount);
    }

    public function testPostUnauthorized()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/v4/projects',
            [
                'json' => [
                    'title' => 'ProjectApiTest Project',
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
