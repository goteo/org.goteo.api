<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Project\ProjectApiResource;
use App\Dto\Matchfunding\MatchCallSubmissionCreationDto;
use App\Entity\Matchfunding\MatchCallSubmission;
use App\Entity\Matchfunding\MatchCallSubmissionStatus;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;

/**
 * MatchCallSubmissions represent the will of a Project to be held under a MatchCall and receive matchfunding financement.
 */
#[API\ApiResource(
    shortName: 'MatchCallSubmission',
    stateOptions: new Options(entityClass: MatchCallSubmission::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
#[API\GetCollection()]
#[API\Post(
    input: MatchCallSubmissionCreationDto::class,
    securityPostDenormalize: 'is_granted("PROJECT_EDIT", object.project)',
    securityPostDenormalizeMessage: 'You do not have permission to submit that Project to a MatchCall'
)]
#[API\Get()]
#[API\Patch(
    security: 'is_granted("MATCHCALLSUBMISSION_EDIT", object)',
    securityMessage: 'You do not have permission to edit this MatchCallSubmission'
)]
class MatchCallSubmissionApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The MatchCall to which this MatchCallSubmission belongs to.
     */
    #[API\ApiProperty(writable: false)]
    public MatchCallApiResource $call;

    /**
     * The Project that applied for the MatchCall.
     */
    #[API\ApiProperty(writable: false)]
    public ProjectApiResource $project;

    /**
     * The status of the Project's application for the MatchCall.\
     * Only MatchCallSubmissions with an status `accepted` will receive matchfunding.
     */
    public MatchCallSubmissionStatus $status = MatchCallSubmissionStatus::DEFAULT;
}
