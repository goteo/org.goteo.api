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

    public function testReleaseDateUpdatesOnCampaignStart(): void
    {
        $owner = new User();
        $owner->setHandle('test_calendar_user');
        $owner->setEmail('testcalendaruser@example.com');
        $owner->setPassword('projectapitestcalendaruserpassword');

        // Create Project
        $project = new Project();
        $project->setTitle('Test Project');
        $project->setSubtitle('Test Project Subtitle');
        $project->setDeadline(ProjectDeadline::Minimum);
        $project->setCategory(Category::LibreSoftware);
        $project->setDescription('Test Project Description');
        $project->setTerritory(new ProjectTerritory('ES'));
        $project->setOwner($owner);
        $project->setStatus(ProjectStatus::InReview);

        $this->entityManager->persist($owner);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        // Change State to IN_CAMPAIGN
        $project->setStatus(ProjectStatus::InCampaign);
        $this->entityManager->flush();

        $this->assertNotNull($project->getCalendar()->release);
    }

    public function testMinimumDeadlineIsSetCorrectly(): void
    {
        $owner = new User();
        $owner->setHandle('test_min_deadline_user');
        $owner->setEmail('testmindeadlineuser@example.com');
        $owner->setPassword('projectapitestmindeadlineuserpassword');

        $project = new Project();
        $project->setTitle('Test Project');
        $project->setSubtitle('Test Project Subtitle');
        $project->setDeadline(ProjectDeadline::Minimum);
        $project->setCategory(Category::LibreSoftware);
        $project->setDescription('Test Project Description');
        $project->setTerritory(new ProjectTerritory('ES'));
        $project->setOwner($owner);
        $project->setStatus(ProjectStatus::InReview);

        $this->entityManager->persist($owner);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $project->setStatus(ProjectStatus::InCampaign);
        $this->entityManager->flush();

        $releaseDate = $project->getCalendar()->release;
        $minimumDeadline = $project->getCalendar()->minimum;

        $this->assertNotNull($minimumDeadline);
        $this->assertGreaterThan($releaseDate, $minimumDeadline);
        $this->assertEquals($releaseDate->modify('+40 days'), $minimumDeadline);
    }
}
