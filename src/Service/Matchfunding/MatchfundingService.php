<?php

namespace App\Service\Matchfunding;

use App\Entity\Accounting\Transaction;
use App\Entity\Gateway\Charge;
use App\Entity\Matchfunding\MatchAgainst;
use App\Entity\Matchfunding\MatchCallSubmission;
use App\Entity\Matchfunding\MatchCallSubmissionStatus;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectDeadline;
use App\Matchfunding\Formula\FormulaLocator;
use App\Matchfunding\Rule\RuleLocator;
use App\Service\Project\BudgetService;
use App\Service\Project\SupportService;
use Doctrine\Common\Collections\Collection;

class MatchfundingService
{
    public const SUBMISSION_ACCEPTED = MatchCallSubmissionStatus::Accepted;

    public function __construct(
        private RuleLocator $ruleLocator,
        private FormulaLocator $formulaLocator,
        private BudgetService $budgetService,
        private SupportService $supportService,
    ) {}

    /**
     * @return Collection<int, MatchCallSubmission>
     */
    public function getAcceptedSubmissions(Project $project): Collection
    {
        return $project->getMatchCallSubmissionsBy(self::SUBMISSION_ACCEPTED);
    }

    /**
     * Perform the match-making logic for a Charge.
     *
     * @return Transaction[] The Transactions from the MatchCalls that should be made for a matcheable Charge
     */
    public function match(Charge $charge): array
    {
        $target = $charge->getTarget()->getOwner();

        if (!$target instanceof Project) {
            return [];
        }

        $transactions = [];

        foreach ($this->getAcceptedSubmissions($target) as $submission) {
            $transactions[] = $this->getTransaction($submission, $charge, $target);
        }

        return $transactions;
    }

    private function getTransaction(
        MatchCallSubmission $submission,
        Charge $charge,
        Project $target,
    ): Transaction {
        $strategy = $submission->getCall()->getStrategy();

        foreach ($this->ruleLocator->getFrom($strategy) as $rule) {
            if (!$rule->validate($charge, $submission)) {
                continue;
            }
        }

        $toBeMatched = match ($strategy->getAgainst()) {
            MatchAgainst::Charge => $charge->getMoney(),
            MatchAgainst::BudgetMin => $this->getBudget($target)[ProjectDeadline::Minimum->value],
            MatchAgainst::BudgetOpt => $this->getBudget($target)[ProjectDeadline::Optimum->value],
        };

        $matched = $this->formulaLocator
            ->get($strategy->getFormulaName())
            ->match($strategy->getFactor(), $toBeMatched, $strategy->getLimit());

        $transaction = new Transaction();
        $transaction->setMoney($matched);
        $transaction->setOrigin($submission->getCall()->getAccounting());
        $transaction->setTarget($submission->getProject()->getAccounting());

        return $transaction;
    }

    private function getBudget(Project $project)
    {
        return $this->budgetService->calcBudget(
            $project->getBudgetItems()->toArray(),
            $project->getAccounting()->getCurrency()
        );
    }
}
