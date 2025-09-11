<?php

namespace App\Benzina;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectCalendar;
use App\Entity\Project\ProjectCategory;
use App\Entity\Project\ProjectDeadline;
use App\Entity\Project\ProjectStatus;
use App\Entity\Project\ProjectVideo;
use App\Entity\Project\Update;
use App\Entity\Territory;
use App\Entity\User\User;
use App\Repository\Project\ProjectRepository;
use App\Repository\User\UserRepository;
use App\Service\Embed\EmbedService;
use App\Service\Project\TerritoryService;
use Doctrine\Common\Collections\ArrayCollection;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class ProjectsPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;
    use DatabasePumpTrait;
    use ProjectsPumpTrait;
    use LocalizedPumpTrait;

    public function __construct(
        private ProjectRepository $projectRepository,
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
        if (\in_array($status, [ProjectStatus::Rejected])) {
            return;
        }

        $created = new \DateTime($record['created']);
        if (\in_array($status, [ProjectStatus::InDraft, ProjectStatus::InEditing]) && $created < new \DateTime('2024-01-01')) {
            return;
        }

        $owner = $this->getProjectOwner($record);
        if ($owner === null) {
            return;
        }

        $project = $this->getProject($record);
        if ($project === null) {
            $project = new Project();
        }

        $project->setSlug($record['id']);
        $project->setCategory($this->getProjectCategory($record));
        $project->setTerritory($this->getProjectTerritory($record));
        $project->setVideo($this->getProjectVideo($record));
        $project->setOwner($owner);
        $project->setStatus($status);
        $project->setMigrated(true);
        $project->setMigratedId($record['id']);
        $project->setDateCreated($created);
        $project->setDateUpdated(new \DateTime());
        $project->setTranslatableLocale($record['lang']);
        $project->setUpdates(new ArrayCollection($this->getProjectUpdates($project, $context)));

        $conf = $this->getProjectConf($project, $context);

        $project->setDeadline($this->getProjectDeadline($conf));
        $project->setCalendar($this->getProjectCalendar($record));

        $project->addLocale($record['lang']);
        $project->setTitle($record['name'] ?? '');
        $project->setSubtitle($record['subtitle'] ?? '');
        $project->setDescription($this->getProjectDescription($record));

        $this->setPreventFlushAndClear(true);
        $this->persist($project, $context);

        $localizations = $this->getProjectLocalizations($project, $context);

        $this->setPreventFlushAndClear(false);
        $this->localize($project, $localizations, $context, [
            'title' => fn($l) => $l['name'],
            'description' => fn($l) => $this->getProjectDescription($l),
        ]);
    }

    private function getProject(array $record): ?Project
    {
        return $this->projectRepository->findOneBy(['migratedId' => $record['id']]);
    }

    private function getProjectOwner(array $record): ?User
    {
        return $this->userRepository->findOneBy(['migratedId' => $record['owner']]);
    }

    private function getProjectDescription(array $record): string
    {
        $lang = $record['lang'];
        $hasTitles = \array_key_exists($lang, self::PROJECT_DESC_TITLES);

        $description = $record['description'];

        if ($hasTitles) {
            $description .= \sprintf("\n\n## %s", self::PROJECT_DESC_TITLES[$lang]['about']);
        }

        $description .= \sprintf("\n%s", $record['about']);

        if ($hasTitles) {
            $description .= \sprintf("\n\n## %s", self::PROJECT_DESC_TITLES[$lang]['motivation']);
        }

        $description .= \sprintf("\n\n%s", $record['motivation']);

        if ($hasTitles) {
            $description .= \sprintf("\n\n## %s", self::PROJECT_DESC_TITLES[$lang]['related']);
        }

        $description .= \sprintf("\n%s", $record['related']);

        return $description;
    }

    private function getProjectLocalizations(Project $project, array $context): array
    {
        $query = $this->getDbConnection($context)->prepare(
            'SELECT * FROM `project_lang` l WHERE l.id = :project'
        );

        $query->execute(['project' => $project->getMigratedId()]);

        return $query->fetchAll();
    }

    private function getProjectCalendar(array $record): ProjectCalendar
    {
        $calendar = new ProjectCalendar();
        $calendar->release = new \DateTimeImmutable($record['published']);
        $calendar->minimum = new \DateTimeImmutable($record['passed'] ?? $record['closed']);
        $calendar->optimum = new \DateTimeImmutable($record['success'] ?? $record['closed']);

        return $calendar;
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
        if ($record['project_location'] === null) {
            return Territory::unknown();
        }

        $cleanAddress = $this->cleanProjectLocation($record['project_location'], 2);

        if ($cleanAddress === '') {
            return Territory::unknown();
        }

        return $this->territoryService->search($cleanAddress);
    }

    private function getProjectVideoSource(array $record): ?string
    {
        if ($record['video'] !== null) {
            return \trim($record['video']);
        }

        if ($record['media'] !== null) {
            return \trim($record['media']);
        }

        return null;
    }

    private function getProjectVideo(array $record): ?ProjectVideo
    {
        $url = $this->getProjectVideoSource($record);

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
            if ($post['publish'] == 0) {
                continue;
            }

            $update = new Update();
            $update->setProject($project);
            $update->setTranslatableLocale($project->getLocales()[0]);
            $update->setTitle($post['title']);
            $update->setSubtitle($post['subtitle'] ?? '');
            $update->setBody($post['text'] ?? '');
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
