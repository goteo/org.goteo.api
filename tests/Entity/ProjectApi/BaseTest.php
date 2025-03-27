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

abstract class BaseTest extends ApiTestCase
{
    use ResetDatabase;
    protected EntityManagerInterface $entityManager;

    private const USER_EMAIL = 'testuser@example.com';
    private const USER_PASSWORD = 'projectapitestuserpassword';
    private const BASE_URI = '/v4/projects';

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    // Auxiliary Methods

    abstract protected function getMethod(): string;

    protected function getUri(int $id = 1): string
    {
        return self::BASE_URI."/$id";
    }

    protected function createTestUser(): User
    {
        $user = new User();
        $user->setHandle('test_user');
        $user->setEmail(self::USER_EMAIL);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        $user->setPassword($passwordHasher->hashPassword($user, self::USER_PASSWORD));

        return $user;
    }

    protected function prepareTestUser(?User $user = null): void
    {
        $user ??= $this->createTestUser();

        $this->entityManager->persist($user);
        $this->entityManager->flush();
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

    protected function createTestProject(): Project
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

    protected function prepareTestProject(?Project $project = null): void
    {
        $project ??= $this->createTestProject();

        $this->entityManager->persist($project);
        $this->entityManager->flush();
    }

    protected function getValidToken(Client $client): string
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

    protected function getHeaders(Client $client): array
    {
        return [
            'Authorization' => 'Bearer '.$this->getValidToken($client),
            'Content-Type' => 'application/json',
        ];
    }

    // Auxiliary Tests

    public function testOneNotFound(): void
    {
        $this->prepareTestProject();

        $client = static::createClient();
        $client->request(
            $this->getMethod(),
            $this->getUri(999),
            ['headers' => $this->getHeaders($client)]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
