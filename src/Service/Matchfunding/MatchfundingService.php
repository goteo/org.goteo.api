<?php

namespace App\Service\Matchfunding;

use App\Entity\Gateway\Charge;
use App\Entity\Matchfunding\MatchAgainst;
use App\Entity\Matchfunding\MatchCallSubmissionStatus;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectDeadline;
use App\Matchfunding\Formula\FormulaLocator;
use App\Service\Project\BudgetService;

class MatchfundingService
{
    public const SUBMISSION_ACCEPTED = MatchCallSubmissionStatus::Accepted;

    public function __construct(
        private FormulaLocator $formulaLocator,
        private BudgetService $budgetService,
    ) {}

    public function match(Charge $charge): void
    {
        $target = $charge->getTarget()->getOwner();

        if (!$target instanceof Project) {
            return;
        }

        foreach ($target->getMatchCallSubmissionsBy(self::SUBMISSION_ACCEPTED) as $submission) {
            $call = $submission->getCall();
            $strategy = $call->getStrategy();

            $toBeMatched = match ($strategy->getAgainst()) {
                MatchAgainst::Charge => $charge->getMoney(),
                MatchAgainst::BudgetMin => $this->getBudget($target)[ProjectDeadline::Minimum],
                MatchAgainst::BudgetOpt => $this->getBudget($target)[ProjectDeadline::Optimum],
            };

            $matched = $this->formulaLocator
                ->get($strategy->getFormulaName())
                ->match($strategy->getFactor(), $toBeMatched, $strategy->getLimit());
        }
    }

    private function getBudget(Project $project)
    {
        return $this->budgetService->calcBudget(
            $project->getBudgetItems()->toArray(),
            $project->getAccounting()->getCurrency()
        );
    }
}
