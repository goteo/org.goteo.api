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

    // region Tests

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
            ['headers' => ['Authorization' => 'Bearer '.$token]]
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@type' => 'Collection']);
    }

    public function testGetCollectionWithoutToken()
    {
        static::createClient()->request('GET', '/v4/projects');

        $this->assertResponseIsSuccessful();
    }

    public function testGetCollection(): void
    {
        $client = static::createClient();

        $token = $this->getValidToken($client);
        $headers = ['headers' => ['Authorization' => 'Bearer '.$token]];

        $client->request('GET', '/v4/projects', $headers);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/v4/projects']);
        $this->assertJsonContains(['@type' => 'Collection']);
        $this->assertJsonContains(['totalItems' => 0]);
        $this->assertJsonContains(['member' => []]);

        $project = new Project();
        $project->setTitle('Test Project');
        $project->setSubtitle('Test Project Subtitle');
        $project->setDeadline(ProjectDeadline::Minimum);
        $project->setCategory(Category::LibreSoftware);
        $project->setDescription('Test Project Description');
        $project->setTerritory(new ProjectTerritory('ES'));
        $project->setOwner($this->owner);
        $project->setStatus(ProjectStatus::InEditing);

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

    // #endregion
}
