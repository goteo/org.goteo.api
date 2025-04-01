<?php

namespace App\Tests\Entity\ProjectApi;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;

class GetOneTest extends BaseTest
{
    // Auxiliary functions

    protected function getMethod(): string
    {
        return 'GET';
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
        $client = static::createClient();
        $client->request('GET', $this->getUri(1), $this->getRequestOptions($client));

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertProjectData($responseData, $project);
    }

    private function testGetOneFilteredByStatus(ProjectStatus $status): void
    {
        $project = $this->createTestProjectOptimized(1, ['status' => $status])[0];

        $this->testSuccessfulGetOneBase($project);
    }

    // Runable Tests

    public function testGetOneWithValidToken(): void
    {
        $project = $this->createTestProjectOptimized(1)[0];

        $this->testSuccessfulGetOneBase($project);
    }

    public function testGetOneFilteredByStatusInFunding(): void
    {
        $this->testGetOneFilteredByStatus(ProjectStatus::InFunding);
    }

    public function testGetOneFilteredByStatusFunded(): void
    {
        $this->testGetOneFilteredByStatus(ProjectStatus::Funded);
    }

    public function testGetOneUnauthorized(): void
    {
        $this->createTestProjectOptimized(1);

        static::createClient()->request('GET', $this->getUri(1));

        $this->assertResponseIsSuccessful();
    }

    public function testGetOneWithInvalidToken(): void
    {
        $this->testInvalidToken($this->getUri(1));
    }

    public function testGetOneNotFound(): void
    {
        $this->testOneNotFound();
    }
}
