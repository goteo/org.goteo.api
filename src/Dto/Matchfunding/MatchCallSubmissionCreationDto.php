<?php

namespace App\Dto\Matchfunding;

use App\ApiResource\Matchfunding\MatchCallApiResource;
use App\ApiResource\Project\ProjectApiResource;
use App\Validator\SingleProjectSubmissionPerCall;
use Symfony\Component\Validator\Constraints as Assert;

#[SingleProjectSubmissionPerCall()]
class MatchCallSubmissionCreationDto
{
    /**
     * The MatchCall to which this MatchCallSubmission belongs to.
     */
    #[Assert\NotBlank()]
    public MatchCallApiResource $call;

    /**
     * The Project that applied for the MatchCall.
     */
    #[Assert\NotBlank()]
    public ProjectApiResource $project;
}
