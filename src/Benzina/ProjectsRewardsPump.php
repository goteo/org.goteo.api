<?php

namespace App\Benzina;

use App\Entity\Money;
use App\Entity\Project\BudgetItemType;
use App\Entity\Project\Project;
use App\Entity\Project\Reward;
use App\Repository\Project\ProjectRepository;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class ProjectsRewardsPump implements PumpInterface
{
    use ArrayPumpTrait;
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
        $reward->setTitle($record['reward']);
        $reward->setDescription($record['description'] ?? $record['reward']);
        $reward->setMoney(new Money($record['amount'] * 100, 'EUR'));
        $reward->setHasUnits($record['units'] > 0);
        $reward->setUnitsTotal($record['units'] ?? 0);
        $reward->setUnitsAvailable($record['units'] ?? 0);

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
}
