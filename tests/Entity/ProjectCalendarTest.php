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
use Zenstruck\Foundry\Test\ResetDatabase;

class ProjectCalendarTest extends ApiTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createTestUser(string $handle = 'test_user', string $email = 'testuser@example.com'): User
    {
        $user = new User();
        $user->setHandle($handle);
        $user->setEmail($email);
        $user->setPassword('projectapitestpassword');

        return $user;
    }

    private function createTestProject(
        User $owner,
        ProjectDeadline $deadline = ProjectDeadline::Minimum,
        ProjectStatus $status = ProjectStatus::InReview,
    ): Project {
        $project = new Project();
        $project->setTitle('Test Project');
        $project->setSubtitle('Test Project Subtitle');
        $project->setDeadline($deadline);
        $project->setCategory(Category::LibreSoftware);
        $project->setDescription('Test Project Description');
        $project->setTerritory(new ProjectTerritory('ES'));
        $project->setOwner($owner);
        $project->setStatus($status);

        $this->entityManager->persist($project);

        return $project;
    }

    public function testReleaseDateUpdatesOnCampaignStart(): void
    {
        $owner = $this->createTestUser('test_calendar_user', 'testcalendaruser@example.com');
        $project = $this->createTestProject($owner);
        $this->entityManager->persist($owner);
        $this->entityManager->flush();

        // Change State to IN_CAMPAIGN
        $project->setStatus(ProjectStatus::InCampaign);
        $this->entityManager->flush();

        $this->assertNotNull($project->getCalendar()->release);
    }

    public function testMinimumDeadlineIsSetCorrectly(): void
    {
        $owner = $this->createTestUser('test_min_deadline_user', 'testmindeadlineuser@example.com');
        $project = $this->createTestProject($owner, ProjectDeadline::Minimum);
        $this->entityManager->persist($owner);
        $this->entityManager->flush();

        $project->setStatus(ProjectStatus::InCampaign);
        $this->entityManager->flush();

        $releaseDate = $project->getCalendar()->release;
        $minimumDeadline = $project->getCalendar()->minimum;

        $this->assertNotNull($minimumDeadline);
        $this->assertGreaterThan($releaseDate, $minimumDeadline);
        $this->assertEquals($releaseDate->modify('+40 days'), $minimumDeadline);
    }

    public function testOptimumDeadlineIsSetCorrectly(): void
    {
        $owner = $this->createTestUser('test_optimum_deadline_user', 'testoptimumdeadlineuser@example.com');
        $project = $this->createTestProject($owner, ProjectDeadline::Optimum);
        $this->entityManager->persist($owner);
        $this->entityManager->flush();

        $project->setStatus(ProjectStatus::InCampaign);
        $this->entityManager->flush();

        $minimumDeadline = $project->getCalendar()->minimum;
        $optimumDeadline = $project->getCalendar()->optimum;

        $this->assertNotNull($optimumDeadline);
        $this->assertGreaterThan($minimumDeadline, $optimumDeadline);
        $this->assertEquals($minimumDeadline->modify('+40 days'), $optimumDeadline);
    }
}
