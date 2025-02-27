<?php

namespace App\Benzina;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\Entity\Project\ProjectTerritory;
use App\Entity\Project\ProjectVideo;
use App\Entity\User\User;
use App\Repository\User\UserRepository;
use App\Service\Embed\EmbedService;
use App\Service\Project\TerritoryService;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class ProjectsPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use ProjectsPumpTrait;

    public function __construct(
        private UserRepository $userRepository,
        private TerritoryService $territoryService,
        private EmbedService $embedService,
    ) {}

    public function supports(mixed $sample): bool
    {
        if ($this->hasAllKeys($sample, self::PROJECT_KEYS)) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record, array $context): void
    {
        if (empty($record['name'])) {
            return;
        }

        $status = $this->getProjectStatus($record);
        if (\in_array($status, [ProjectStatus::InEditing, ProjectStatus::Rejected])) {
            return;
        }

        $owner = $this->getProjectOwner($record);
        if ($owner === null) {
            return;
        }

        $project = new Project();
        $project->setTranslatableLocale($record['lang']);
        $project->setTitle($record['name']);
        $project->setSubtitle($record['subtitle']);
        $project->setTerritory($this->getProjectTerritory($record));
        $project->setDescription($record['description']);
        $project->setVideo($this->getProjectVideo($record));
        $project->setOwner($owner);
        $project->setStatus($status);
        $project->setMigrated(true);
        $project->setMigratedId($record['id']);
        $project->setDateCreated(new \DateTime($record['created']));
        $project->setDateUpdated(new \DateTime());

        $this->persist($project, $context);
    }

    private function getProjectOwner(array $record): ?User
    {
        return $this->userRepository->findOneBy(['migratedId' => $record['owner']]);
    }

    private function getProjectStatus(array $record): ProjectStatus
    {
        switch ($record['status']) {
            case 1:
                return ProjectStatus::InEditing;
            case 2:
                return ProjectStatus::InReview;
            case 0:
                return ProjectStatus::Rejected;
            case 3:
                return ProjectStatus::InCampaign;
            case 6:
                return ProjectStatus::Unfunded;
            case 4:
                return ProjectStatus::InFunding;
            case 5:
                return ProjectStatus::Fulfilled;
        }
    }

    private function getProjectTerritory(array $record): ProjectTerritory
    {
        $cleanAddress = $this->cleanProjectLocation($record['project_location'], 2);

        if ($cleanAddress === '') {
            return ProjectTerritory::unknown();
        }

        return $this->territoryService->search($cleanAddress);
    }

    private function getProjectVideo(array $record): ?ProjectVideo
    {
        if ($record['video'] === null) {
            return null;
        }

        $url = \trim($record['video']);

        if ($url === '') {
            return null;
        }

        if (!\str_contains($url, '.')) {
            return null;
        }

        try {
            $video = $this->embedService->getVideo($url);

            return new ProjectVideo($video->src, $video->thumbnail);
        } catch (\Exception $e) {
            return null;
        }
    }
}
