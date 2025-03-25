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
        Category $category = Category::LibreSoftware,
    ): Project {
        $project = new Project();
        $project->setTitle($title);
        $project->setSubtitle($subtitle);
        $project->setDeadline(ProjectDeadline::Minimum);
        $project->setCategory($category);
        $project->setDescription($description);
        $project->setTerritory(new ProjectTerritory('ES'));
        $project->setOwner($this->owner);
        $project->setStatus(ProjectStatus::InEditing);

        return $project;
    }

    private function createMultipleProjects(int $number = 20): void
    {
        $this->entityManager->persist($this->owner);
        for ($i = 1; $i <= $number; ++$i) {
            $project = $this->createTestProject("Test Project $i", "Subtitle $i", "Description $i");

            $this->entityManager->persist($project);
        }

        $this->entityManager->flush();
    }

    private function getNumberOfCreationProjects(int $page = 2): int
    {
        $pageSize = 30;

        return $pageSize * $page - $pageSize / 2;
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

        $page = 2;
        $numberOfProjects = $this->getNumberOfCreationProjects($page);
        $this->createMultipleProjects($numberOfProjects);

        // Request the project page
        $response = $client->request('GET', "/v4/projects?page=$page", $headers);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@type' => 'Collection']);
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
        $memberCount = count($data['member']);
        $totalItems = $data['totalItems'];
        $this->assertGreaterThan($memberCount, $totalItems);
    }

    public function testGetCollectionDefaultsToFirstPage(): void
    {
        $client = static::createClient();
        $token = $this->getValidToken($client);
        $headers = ['headers' => ['Authorization' => "Bearer $token"]];

        $numberOfProjects = $this->getNumberOfCreationProjects();
        $this->createMultipleProjects($numberOfProjects);

        $response = $client->request('GET', '/v4/projects', $headers);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@type' => 'Collection']);

        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
        $memberCount = count($data['member']);

        // Verify that you are returning the first page
        $this->assertGreaterThan(0, $memberCount, 'Answer must contain projects on first page');

        // Verify that the first project has same title than the last project created (descending order)
        $this->assertStringContainsString(
            "Test Project $numberOfProjects",
            $data['member'][0]['title']
        );
    }

    private function testGetCollectionFilteredBy(string $filterName, mixed $filterValue, string $urlParamName): void
    {
        $client = static::createClient();
        $token = $this->getValidToken($client);
        $headers = ['headers' => ['Authorization' => "Bearer $token"]];

        // Create 5 generic projects and one with the specific filter value
        $this->createMultipleProjects(5);
        $project = $this->createTestProject();
        $project->{$filterName}($filterValue);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        // Convert enum to string if it's an enum, otherwise use the string directly
        $filterValueName = is_object($filterValue) && isset($filterValue->value)
            ? $filterValue->value : $filterValue;

        // Make the request filtered by the parameter
        $url = "/v4/projects?$urlParamName={$filterValueName}";
        $response = $client->request('GET', $url, $headers);

        // Verify that the answer is successful
        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        // Verify that the project with the filter value is present in the results
        $this->assertGreaterThan(0, count($data['member']));
        $this->assertEquals($filterValueName, $data['member'][0][$urlParamName]);
    }

    public function testGetCollectionFilteredByTitle(): void
    {
        $this->testGetCollectionFilteredBy('setTitle', 'Free Software Project', 'title');
    }

    public function testGetCollectionFilteredByCategory(): void
    {
        $this->testGetCollectionFilteredBy('setCategory', Category::Education, 'category');
    }

    public function testGetCollectionFilteredByStatus(): void
    {
        $this->testGetCollectionFilteredBy('setStatus', ProjectStatus::InCampaign, 'status');
    }

    public function testPostUnauthorized(): void
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
