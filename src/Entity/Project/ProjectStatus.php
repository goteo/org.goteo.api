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
     * Owner finished editing and Project is ready for review.
     */
    case ToReview = 'to_review';

    /**
     * An admin is reviewing the Project.
     */
    case InReview = 'in_review';

    /**
     * Admin asked for changes and the Project is under edition by it's owner.
     */
    case InEditing = 'in_editing';

    /**
     * An admin reviewed it and rejected it. Final.
     */
    case Rejected = 'rejected';

    /**
     * An admin reviewed it and deemed worthy to campaign.
     */
    case ToCampaign = 'to_campaign';

    /**
     * Project was reviewed and is in campaign for funding.
     */
    case InCampaign = 'in_campaign';

    /**
     * Project finished campaigning but didn't meet funding goals. Final.
     */
    case Unfunded = 'unfunded';

    /**
     * Project successfully finished campaigning and owner can receive funds.
     */
    case InFunding = 'in_funding';

    /**
     * Project owner received all raised funds. Final.
     */
    case Funded = 'funded';
}
