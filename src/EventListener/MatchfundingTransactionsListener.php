<?php

namespace App\EventListener;

use App\Entity\Accounting\Transaction;
use App\Entity\Matchfunding\MatchCallSubmissionStatus;
use App\Entity\Project\Project;
use App\Matchfunding\Formula\FormulaLocator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::postPersist,
    method: 'processTransaction',
    entity: Transaction::class
)]
final class MatchfundingTransactionsListener
{
    public function __construct(
        private FormulaLocator $formulaLocator,
    ) {}

    /**
     * Generates matched Transactions for Transactions inside a MatchCall.
     */
    public function processTransaction(
        Transaction $transaction,
        PostPersistEventArgs $event,
    ) {
        $target = $transaction->getTarget()->getOwner();

        if (!$target instanceof Project) {
            return;
        }

        $submissions = $target->getMatchCallSubmissions();

        foreach ($submissions as $submission) {
            if ($submission->getStatus() !== MatchCallSubmissionStatus::Accepted) {
                continue;
            }

            // $call = $submission->getCall();
            // $strategy = $this->formulaLocator->get($call->getFormulaName());
            // $match = $strategy->match($transaction->getMoney());

            // if ($match->getId() !== null) {
            //     return;
            // }

            // $event->getObjectManager()->persist($match);
            // $event->getObjectManager()->flush();
        }
    }
}
