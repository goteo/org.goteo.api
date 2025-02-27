<?php

namespace App\Benzina;

use App\Entity\Money;
use App\Entity\Project\BudgetItem;
use App\Entity\Project\BudgetItemType;
use App\Entity\Project\Project;
use App\Repository\Project\ProjectRepository;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class ProjectsBudgetPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;

    private const COST_KEYS = [
        'id',
        'project',
        'cost',
        'description',
        'type',
        'amount',
        'required',
        'from',
        'until',
        'order',
    ];

    public function __construct(
        private ProjectRepository $projectRepository,
    ) {}

    public function supports(mixed $sample): bool
    {
        if ($this->hasAllKeys($sample, self::COST_KEYS)) {
            return true;
        }

        return false;
    }

    public function pump(mixed $record, array $context): void
    {
        if (empty($record['cost']) || empty($record['amount'])) {
            return;
        }

        $project = $this->getBudgetProject($record);
        if ($project === null) {
            return;
        }

        $budgetItem = new BudgetItem();
        $budgetItem->setTranslatableLocale($project->getLocales()[0]);
        $budgetItem->setProject($project);
        $budgetItem->setType($this->getCostType($record));
        $budgetItem->setTitle($record['cost']);
        $budgetItem->setDescription($record['description'] ?? $record['cost']);

        $money = new Money($record['amount'] * 100, 'EUR');

        if ($record['required'] > 0) {
            $budgetItem->setOptimum($money);
        } else {
            $budgetItem->setMinimum($money);
        }

        $this->persist($budgetItem, $context);
    }

    private function getBudgetProject(array $record): ?Project
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
