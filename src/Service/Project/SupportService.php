<?php

namespace App\Service\Project;

use App\Entity\Gateway\Charge;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
use App\Entity\User\User;

class SupportService
{
    /**
     * Create ProjectSupport for the given data.
     *
     * @param Charge[] $charges
     */
    public function createSupport(Project $project, User $owner, array $charges): Support
    {
        $projectSupport = new Support();
        $projectSupport->setProject($project);
        $projectSupport->setOrigin($owner->getAccounting());
        $projectSupport->setAnonymous(false);

        foreach ($charges as $charge) {
            $projectSupport->addTransactions($charge->getTransactions());
        }

        return $projectSupport;
    }
}
