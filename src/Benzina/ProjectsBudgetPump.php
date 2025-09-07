<?php

namespace App\Benzina;

use App\Entity\EmbeddableMoney;
use App\Entity\Project\BudgetItem;
use App\Entity\Project\BudgetItemType;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectDeadline;
use App\Repository\Project\ProjectRepository;
use Gedmo\Translatable\Entity\Translation;
use Goteo\Benzina\Pump\ArrayPumpTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;
use Goteo\Benzina\Pump\PumpInterface;

class ProjectsBudgetPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DatabasePumpTrait;
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

        $project = $this->getProject($record);
        if ($project === null) {
            return;
        }

        $budgetItem = new BudgetItem();
        $budgetItem->setMigrated(true);
        $budgetItem->setMigratedId($record['id']);
        $budgetItem->setTranslatableLocale($project->getLocales()[0]);
        $budgetItem->setProject($project);
        $budgetItem->setType($this->getCostType($record));
        $budgetItem->setTitle($record['cost']);
        $budgetItem->setDescription($record['description'] ?? $record['cost']);
        $budgetItem->setMoney(new EmbeddableMoney($record['amount'] * 100, 'EUR'));
        $budgetItem->setDeadline($this->getDeadline($record));

        $localizations = $this->getCostLocalizations($budgetItem, $context);
        $translations = $this->entityManager->getRepository(Translation::class);
        foreach ($localizations as $localization) {
            $locale = $localization['lang'];

            $budgetItem->addLocale($locale);
            $translations
                ->translate($budgetItem, 'title', $locale, $localization['cost'] ?? $record['cost'])
                ->translate($budgetItem, 'description', $locale, $localization['description'] ?? $record['description'])
            ;
        }

        $this->persist($budgetItem, $context);
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

    private function getDeadline(array $record): ProjectDeadline
    {
        if ($record['required'] > 0) {
            return ProjectDeadline::Minimum;
        }

        return ProjectDeadline::Optimum;
    }

    private function getCostLocalizations(BudgetItem $budgetItem, array $context): array
    {
        $query = $this->getDbConnection($context)->prepare(
            'SELECT * FROM `cost_lang` l WHERE l.id = :cost'
        );

        $query->execute(['cost' => $budgetItem->getMigratedId()]);

        return $query->fetchAll();
    }
}
