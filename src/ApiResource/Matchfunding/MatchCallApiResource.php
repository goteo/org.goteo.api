<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Matchfunding\MatchCall;
use App\Entity\Territory;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A MatchCall is an owned and managed event which accepts MatchCallSubmissions from Projects to receive *matchfunding* financement.
 * This means any money inside a Transaction going to a Project in a MatchCall will be matched with funds from the MatchCall accounting.
 * \
 * \
 * MatchCallSubmissions from Projects can be accepted or rejected by the managers.
 * They can also choose from predefined strategies and tune them to perform the matching.
 */
#[API\ApiResource(
    shortName: 'MatchCall',
    stateOptions: new Options(entityClass: MatchCall::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
class MatchCallApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The Accounting which holds and spends the funds for this MatchCall.
     */
    #[API\ApiProperty(writable: false)]
    public AccountingApiResource $accounting;

    /**
     * A list of the MatchCallSubmissions received by this MatchCall.
     *
     * @var MatchCallSubmissionApiResource[]
     */
    #[API\ApiProperty(writable: false)]
    public array $submissions;

    /**
     * A list of Users who can modify this MatchCall.
     *
     * @var UserApiResource[]
     */
    public array $managers;

    /**
     * Main display title.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Long-form secondary display text.
     */
    public string $description;

    /**
     * Codes for the territory of interest in this MatchCall.
     */
    #[Assert\NotBlank()]
    #[Assert\Valid]
    public Territory $territory;
}
