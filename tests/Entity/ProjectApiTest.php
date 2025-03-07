<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
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

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testGetCollection(): void
    {
        static::createClient()->request('GET', '/v4/projects');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/v4/projects']);
        $this->assertJsonContains(['@type' => 'Collection']);
        $this->assertJsonContains(['totalItems' => 0]);
        $this->assertJsonContains(['member' => []]);

        $owner = new User();
        $owner->setHandle('test_user');
        $owner->setEmail('testuser@example.com');
        $owner->setPassword('projectapitestuserpassword');

        $project = new Project();
        $project->setTitle('Test Project');
        $project->setSubtitle('Test Project Subtitle');
        $project->setDeadline(ProjectDeadline::Minimum);
        $project->setCategory(Category::LibreSoftware);
        $project->setDescription('Test Project Description');
        $project->setTerritory(new ProjectTerritory('ES'));
        $project->setOwner($owner);
        $project->setStatus(ProjectStatus::InEditing);

        $this->entityManager->persist($owner);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        static::createClient()->request('GET', '/v4/projects');

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
}
