<?php

namespace App\Benzina;

use App\Entity\EmbeddableMoney;
use App\Entity\Project\BudgetItemType;
use App\Entity\Project\Project;
use App\Entity\Project\Reward;
use App\Repository\Project\ProjectRepository;
use Gedmo\Translatable\Entity\Translation;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class ProjectsRewardsPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DatabasePumpTrait;
    use DoctrinePumpTrait;

    private const REWARD_KEYS = [
        'id',
        'project',
        'reward',
        'description',
        'type',
        'icon',
        'other',
        'license',
        'amount',
        'units',
        'fulsocial',
        'url',
        'order',
        'bonus',
        'category',
        'extra_info_message',
        'subscribable',
    ];

    public function __construct(
        private ProjectRepository $projectRepository,
    ) {}

    public function supports(mixed $sample): bool
    {
        if ($this->hasAllKeys($sample, self::REWARD_KEYS)) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record, array $context): void
    {
        if (empty($record['reward']) || empty($record['amount'])) {
            return;
        }

        if ($record['type'] !== 'individual') {
            return;
        }

        $project = $this->getProject($record);
        if ($project === null) {
            return;
        }

        $reward = new Reward();
        $reward->setTranslatableLocale($project->getLocales()[0]);
        $reward->setProject($project);
        $reward->setMigrated(true);
        $reward->setMigratedId($record['id']);
        $reward->setTitle($record['reward']);
        $reward->setDescription($record['description'] ?? $record['reward']);
        $reward->setMoney(new EmbeddableMoney($record['amount'] * 100, 'EUR'));
        $reward->setHasUnits($record['units'] > 0);
        $reward->setUnitsTotal($record['units'] ?? 0);
        $reward->setUnitsAvailable($record['units'] ?? 0);

        $localizations = $this->getRewardLocalizations($reward, $context);
        $translations = $this->entityManager->getRepository(Translation::class);
        foreach ($localizations as $localization) {
            $locale = $localization['lang'];

            $reward->addLocale($locale);
            $translations
                ->translate($reward, 'title', $locale, $localization['reward'] ?? $record['reward'])
                ->translate($reward, 'description', $locale, $localization['description'] ?? $record['description'])
            ;
        }

        $this->persist($reward, $context);
    }

    private function getProject(array $record): ?Project
    {
        return $this->projectRepository->findOneBy(['migratedId' => $record['project']]);
    }

    private function getCostType(array $record): BudgetItemType
    {
        switch ($record['type']) {
            case 'task':
                return BudgetItemType::Task;
            case 'material':
                return BudgetItemType::Material;
            default:
                return BudgetItemType::Infrastructure;
        }
    }

    private function getRewardLocalizations(Reward $reward, array $context): array
    {
        $query = $this->getDbConnection($context)->prepare(
            'SELECT * FROM `reward_lang` l WHERE l.id = :reward'
        );

        $query->execute(['reward' => $reward->getMigratedId()]);

        return $query->fetchAll();
    }
}
