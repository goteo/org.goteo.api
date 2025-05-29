<?php

namespace App\Service\Project;

use App\Entity\Accounting\Accounting;
use App\Entity\Gateway\Charge;
use App\Entity\Project\Project;
use App\Entity\Project\Support;

class SupportService
{
    /**
     * Create ProjectSupport for the given data.
     *
     * @param Charge[] $charges
     */
    public function createSupport(
        Project $project,
        Accounting $origin,
        array $charges = [],
    ): Support {
        $projectSupport = new Support();
        $projectSupport->setProject($project);
        $projectSupport->setOrigin($origin);
        $projectSupport->setAnonymous(false);

        foreach ($charges as $charge) {
            $projectSupport->addTransactions($charge->getTransactions()->toArray());
        }

        return $projectSupport;
    }
}
