<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Gateway\ChargeApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Project\RewardClaim;
use App\State\ApiResourceStateProvider;
use App\State\Project\RewardClaimStateProcessor;
use App\Validator\AvailableRewardUnits;
use App\Validator\EnoughRewardCharge;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A ProjectRewardClaim represents the will of an User who wishes to obtain one ProjectReward.
 */
#[API\ApiResource(
    shortName: 'ProjectRewardClaim',
    stateOptions: new Options(entityClass: RewardClaim::class),
    provider: ApiResourceStateProvider::class,
    processor: RewardClaimStateProcessor::class
)]
#[EnoughRewardCharge()]
class RewardClaimApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The User claiming the ProjectReward.
     */
    #[API\ApiProperty(writable: false)]
    public UserApiResource $owner;

    /**
     * The GatewayCharge granting access to the ProjectReward.
     */
    #[Assert\NotBlank()]
    public ChargeApiResource $charge;

    /**
     * The ProjectReward being claimed.
     */
    #[Assert\NotBlank()]
    #[AvailableRewardUnits()]
    public RewardApiResource $reward;
}
