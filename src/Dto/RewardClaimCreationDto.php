<?php

namespace App\Dto;

use App\ApiResource\Gateway\ChargeApiResource;
use App\ApiResource\Project\RewardApiResource;
use App\Validator\AvailableRewardUnits;
use App\Validator\EnoughRewardCharge;
use App\Validator\SameProjectRewardCharge;
use App\Validator\SingleRewardClaimPerCharge;
use Symfony\Component\Validator\Constraints as Assert;

#[EnoughRewardCharge()]
#[SameProjectRewardCharge()]
#[SingleRewardClaimPerCharge()]
class RewardClaimCreationDto
{
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
