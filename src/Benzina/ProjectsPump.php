<?php

namespace App\Benzina;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectCategory;
use App\Entity\Project\ProjectDeadline;
use App\Entity\Project\ProjectStatus;
use App\Entity\Project\ProjectVideo;
use App\Entity\Project\Update;
use App\Entity\Territory;
use App\Entity\User\User;
use App\Repository\User\UserRepository;
use App\Service\Embed\EmbedService;
use App\Service\Project\CalendarService;
use App\Service\Project\TerritoryService;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class ProjectsPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use DatabasePumpTrait;
    use ProjectsPumpTrait;

    public function __construct(
        private UserRepository $userRepository,
        private TerritoryService $territoryService,
        private EmbedService $embedService,
        private CalendarService $calendarService,
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
        $project->setSlug($record['id']);
        $project->setSubtitle($record['subtitle']);
        $project->setCategory($this->getProjectCategory($record));
        $project->setTerritory($this->getProjectTerritory($record));
        $project->setDescription($record['description']);
        $project->setVideo($this->getProjectVideo($record));
        $project->setOwner($owner);
        $project->setStatus($status);
        $project->setMigrated(true);
        $project->setMigratedId($record['id']);
        $project->setDateCreated(new \DateTime($record['created']));
        $project->setDateUpdated(new \DateTime());

        $updates = $this->getProjectUpdates($project, $context);
        foreach ($updates as $update) {
            $project->addUpdate($update);
        }

        $conf = $this->getProjectConf($project, $context);

        $project->setDeadline($this->getProjectDeadline($conf));
        $project->setCalendar($this->calendarService->makeCalendar(
            $project->getDeadline(),
            new \DateTimeImmutable($record['published']),
            $conf['days_round1'],
            $conf['days_round2'],
        ));

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
            case 3:
                return ProjectStatus::InCampaign;
            case 6:
                return ProjectStatus::Unfunded;
            case 4:
            case 5:
                return ProjectStatus::Funded;
            case 0:
            default:
                return ProjectStatus::Rejected;
        }
    }

    private function getProjectCategory(array $record): ProjectCategory
    {
        switch ($record['social_commitment']) {
            case 1:
                return ProjectCategory::Solidary;
            case 2:
                return ProjectCategory::LibreSoftware;
            case 3:
            case 16:
                return ProjectCategory::Employment;
            case 5:
                return ProjectCategory::Journalism;
            case 6:
                return ProjectCategory::Education;
            case 7:
                return ProjectCategory::Culture;
            case 8:
            case 15:
                return ProjectCategory::Ecology;
            case 11:
            case 12:
                return ProjectCategory::Democracy;
            case 13:
                return ProjectCategory::Equity;
            case 14:
                return ProjectCategory::HealthCares;
            case 10:
            default:
                return ProjectCategory::OpenData;
        }
    }

    private function getProjectTerritory(array $record): Territory
    {
        $cleanAddress = $this->cleanProjectLocation($record['project_location'], 2);

        if ($cleanAddress === '') {
            return Territory::unknown();
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

    /**
     * @return Update[]
     */
    private function getProjectUpdates(Project $project, array $context): array
    {
        $updates = [];

        $milestones = $this->getProjectMilestones($project, $context);
        foreach ($milestones as $milestone) {
            $update = new Update();
            $update->setProject($project);
            $update->setTranslatableLocale($project->getLocales()[0]);
            $update->setTitle($milestone['title']);
            $update->setSubtitle('');
            $update->setBody('');
            $update->setDate(new \DateTime($milestone['date']));

            $updates[] = $update;
        }

        $posts = $this->getProjectBlogPosts($project, $context);
        foreach ($posts as $post) {
            $update = new Update();
            $update->setProject($project);
            $update->setTranslatableLocale($project->getLocales()[0]);
            $update->setTitle($post['title']);
            $update->setSubtitle($post['subtitle'] ?? '');
            $update->setBody($post['text']);
            $update->setDate(new \DateTime($post['date']));

            $updates[] = $update;
        }

        return $updates;
    }

    private function getProjectMilestones(Project $project, array $context): array
    {
        $query = $this->getDbConnection($context)->prepare(
            'SELECT p.description AS title, p.image, m.date FROM `milestone` p
                INNER JOIN `project_milestone` m ON m.milestone = p.id
                WHERE m.project = :project
            '
        );

        $query->execute(['project' => $project->getMigratedId()]);

        return $query->fetchAll();
    }

    private function getProjectBlogPosts(Project $project, array $context): array
    {
        $query = $this->getDbConnection($context)->prepare(
            "SELECT * FROM `post` p
                INNER JOIN `blog` b ON b.id = p.blog
                WHERE b.type = 'project'
                    AND b.owner = :project
            "
        );

        $query->execute(['project' => $project->getMigratedId()]);

        return $query->fetchAll();
    }

    private function getProjectConf(Project $project, array $context): array
    {
        $query = $this->getDbConnection($context)->prepare(
            'SELECT * FROM `project_conf` WHERE `project` = :project'
        );

        $query->execute(['project' => $project->getMigratedId()]);

        $conf = $query->fetch();

        if (!\is_array($conf)) {
            return [
                'one_round' => 0,
                'days_round1' => 40,
                'days_round2' => 40,
            ];
        }

        return $conf;
    }

    private function getProjectDeadline(array $conf): ProjectDeadline
    {
        if ($conf['one_round'] < 1) {
            return ProjectDeadline::Optimum;
        }

        return ProjectDeadline::Minimum;
    }
}
