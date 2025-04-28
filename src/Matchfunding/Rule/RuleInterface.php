<?php

namespace App\Matchfunding\Rule;

use App\Entity\Gateway\Charge;
use App\Entity\Project\Project;

interface RuleInterface
{
    /**
     * A plain-text description about what the rules validates for.
     */
    public static function getDescription(): string;

    /**
     * Do necessary checks to determine if Charge and Project comply with this rule.
     *
     * @param Charge  $charge  The Charge that triggered the match making
     * @param Project $project The Project that the Charge is going to
     *
     * @return bool Whether or not this rule is met
     */
    public function validate(Charge $charge, Project $project): bool;
}
