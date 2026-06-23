<?php

namespace App\Service\Project;

use App\Entity\Project\Review;
use App\Entity\Project\ReviewArea;
use App\Entity\Project\ReviewType;

class ReviewService
{
    public function makeReview(ReviewType $type): Review
    {
        $review = new Review();
        $review->setType($type);

        /** @var ReviewArea[] */
        $areas = match ($type) {
            ReviewType::Campaign => $this->getCampaignReviewAreas(),
            ReviewType::Financial => $this->getFinancialReviewAreas(),
        };

        foreach ($areas as $area) {
            $review->addArea($area);
        }

        return $review;
    }

    /**
     * @return ReviewArea[]
     */
    private function getCampaignReviewAreas(): array
    {
        $config = new ReviewArea();
        $config->setTitle('project.configuration');

        $info = new ReviewArea();
        $info->setTitle('project.info');

        $rewards = new ReviewArea();
        $rewards->setTitle('project.rewards');

        $collabs = new ReviewArea();
        $collabs->setTitle('project.collaborations');

        $budget = new ReviewArea();
        $budget->setTitle('project.budget');

        $about = new ReviewArea();
        $about->setTitle('project.about');

        return [
            $config,
            $info,
            $rewards,
            $collabs,
            $budget,
            $about,
        ];
    }

    /**
     * @return ReviewArea[]
     *
     * @todo Add financial review areas
     */
    private function getFinancialReviewAreas(): array
    {
        return [];
    }
}
