<?php

namespace App\Entity\Project;

/**
 * Projects have a start and an end, and in the meantime they go through different phases represented under this status.
 */
enum ProjectStatus: string
{
    /**
     * Project has just been created.
     */
    case InDraft = 'in_draft';

    /**
     * Owner finished editing and Project is ready for campaign review.
     */
    case ToCampaignReview = 'to_campaign_review';

    /**
     * A reviewer is reviewing the Project for campaign.
     */
    case InCampaignReview = 'in_campaign_review';

    /**
     * Owner requested to do changes by the campaign reviewer.
     */
    case InCampaignReviewRequestChange = 'in_campaign_review.request_change';

    /**
     * Project is KO for campaign by the reviewer, final.
     */
    case CampaignReviewRejected = 'campaign_review.rejected';

    /**
     * Project is OK for campaign by the reviewer, can move forward.
     */
    case ToCampaign = 'to_campaign';

    /**
     * Project is in live campaign and raising funds.
     */
    case InCampaign = 'in_campaign';

    /**
     * Project failed to raise enough funds, final.
     */
    case CampaignFailed = 'campaign.failed';

    /**
     * Project raised enough funds, can move forward.
     */
    case ToFundingReview = 'to_funding_review';

    /**
     * Project is under financial review.
     */
    case InFundingReview = 'in_funding_review';

    /**
     * Owner requested to do changes by the financial reviewer.
     */
    case InFundingReviewRequestChange = 'in_funding_review.request_change';

    /**
     * Project is KO for funding by the reviewer, final.
     */
    case FundinReviewRejected = 'funding_review.rejected';

    /**
     * Project is OK for funding by the reviewer, can move forward.
     */
    case ToFunding = 'to_funding';

    /**
     * Project is being funded.
     */
    case InFunding = 'in_funding';

    /**
     * Project's funds were paid. Final.
     */
    case FundingPaid = 'funding.paid';
}
