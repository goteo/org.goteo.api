<?php

namespace App\Tests\Entity\ProjectApi;

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

class GetOneTest extends ApiTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';
    private const BASE_URI = '/v4/projects';

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    // Auxiliary functions

    private function getUri(int $id = 1): string
    {
        return self::BASE_URI."/$id";
    }

    private function createTestUser(): User
    {
        $user = new User();
        $user->setHandle('test_user');
        $user->setEmail(self::USER_EMAIL);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        $user->setPassword($passwordHasher->hashPassword($user, self::USER_PASSWORD));

        return $user;
    }

    private function getExampleProjectData(): array
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

    private function createTestProject(): Project
    {
        $example = $this->getExampleProjectData();

        $project = new Project();
        $project->setTitle($example['title']);
        $project->setSubtitle($example['subtitle']);
        $project->setCategory($example['category']);
        $project->setTerritory(new ProjectTerritory($example['territory']['country']));
        $project->setDescription($example['description']);
        $project->setDeadline($example['deadline']);
        $project->setOwner($this->createTestUser());
        $project->setStatus($example['status']);

        return $project;
    }

    private function prepareTestProject(?Project $project = null): void
    {
        $project ??= $this->createTestProject();

        $this->entityManager->persist($project);
        $this->entityManager->flush();
    }

    private function getValidToken(Client $client): string
    {
        $client->request(
            'POST',
            '/v4/user_tokens',
            [
                'json' => [
                    'identifier' => self::USER_EMAIL,
                    'password' => self::USER_PASSWORD,
                ],
            ]
        );

        return json_decode($client->getResponse()->getContent(), true)['token'];
    }

    private function getHeaders(Client $client): array
    {
        return [
            'Authorization' => 'Bearer '.$this->getValidToken($client),
            'Content-Type' => 'application/json',
        ];
    }

    private function getSerializedProject(Project $project)
    {
        return [
            'id' => $project->getId(),
            'title' => $project->getTitle(),
            'subtitle' => $project->getSubtitle(),
            'category' => $project->getCategory()->value,
            'territory' => ['country' => $project->getTerritory()->country],
            'description' => $project->getDescription(),
            'deadline' => $project->getDeadline()->value,
            'status' => $project->getStatus()->value,
        ];
    }

    private function assertProjectData(array $responseData, Project $project): void
    {
        $expectedData = $this->getSerializedProject($project);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArraySubset($expectedData, $responseData);
    }

    // TESTS

    // Auxiliary Tests

    private function testSuccessfulGetOneBase(Project $project): void
    {
        $this->prepareTestProject($project);

        $client = static::createClient();
        $client->request('GET', $this->getUri(), ['headers' => $this->getHeaders($client)]);

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertProjectData($responseData, $project);
    }

    // Runable Tests

    public function testGetOneWithValidToken(): void
    {
        $project = $this->createTestProject();

        $this->testSuccessfulGetOneBase($project);
    }

    public function testGetOneFilteredByStatus(): void
    {
        $project = $this->createTestProject()->setStatus(ProjectStatus::InFunding);

        $this->testSuccessfulGetOneBase($project);
    }

    public function testGetOneUnauthorized(): void
    {
        $this->prepareTestProject();

        static::createClient()->request('GET', $this->getUri());

        $this->assertResponseIsSuccessful();
    }

    public function testGetOneWithInvalidToken(): void
    {
        static::createClient()->request(
            'GET',
            $this->getUri(),
            ['headers' => ['Authorization' => 'Bearer 123']]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetOneNotFound(): void
    {
        $this->prepareTestProject();

        $client = static::createClient();
        $client->request('GET', $this->getUri(999), ['headers' => $this->getHeaders($client)]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
