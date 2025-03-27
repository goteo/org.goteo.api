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

    private function createTestProject(): Project
    {
        $project = new Project();
        $project->setTitle('Test Project');
        $project->setSubtitle('Test Project Subtitle');
        $project->setDeadline(ProjectDeadline::Minimum);
        $project->setCategory(Category::LibreSoftware);
        $project->setDescription('Test Project Description');
        $project->setTerritory(new ProjectTerritory('ES'));
        $project->setOwner($this->createTestUser());
        $project->setStatus(ProjectStatus::InEditing);

        return $project;
    }

    private function prepareTestProject(): void
    {
        $project = $this->createTestProject();

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

    private function assertProjectData(array $responseData): void
    {
        $expectedData = [
            'id' => 1,
            'title' => 'Test Project',
            'subtitle' => 'Test Project Subtitle',
            'category' => Category::LibreSoftware->value,
            'territory' => ['country' => 'ES'],
            'description' => 'Test Project Description',
            'deadline' => ProjectDeadline::Minimum->value,
            'status' => ProjectStatus::InEditing->value,
        ];

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArraySubset($expectedData, $responseData);
    }

    // TESTS

    public function testGetOneWithValidToken(): void
    {
        $this->prepareTestProject();

        $client = static::createClient();
        $client->request('GET', $this->getUri(), ['headers' => $this->getHeaders($client)]);

        $this->assertResponseIsSuccessful();
        $this->assertProjectData(json_decode($client->getResponse()->getContent(), true));
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
}
