<?php

namespace App\EventListener;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\Entity\Project\Review;
use App\Entity\Project\ReviewType;
use App\Service\Project\CalendarService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Listens for status changes in Projects to apply side-effects for those statuses.
 */
#[AsEntityListener(
    event: Events::preUpdate,
    method: 'preUpdate',
    entity: Project::class
)]
final class ProjectStatusListener
{
    public function __construct(
        private CalendarService $calendarService,
    ) {}

    public function preUpdate(
        Project $project,
        PreUpdateEventArgs $event,
    ) {
        if (!$event->hasChangedField('status')) {
            return;
        }

        $project = match ($project->getStatus()) {
            ProjectStatus::InCampaign => $this->statusInCampaign($project),
            ProjectStatus::ToCampaignReview => $this->statusToCampaignReview($project),
        };
    }

    private function statusInCampaign(Project $project): Project
    {
        $calendar = $this->calendarService->makeCalendar($project->getDeadline());

        $project->setCalendar($calendar);

        return $project;
    }

    private function statusToCampaignReview(Project $project): Project
    {
        $review = new Review();
        $review->setType(ReviewType::Campaign);

        $project->addReview($review);
    }
}
