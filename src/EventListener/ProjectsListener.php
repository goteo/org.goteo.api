<?php

namespace App\EventListener;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectStatus;
use App\Service\Project\CalendarService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::preUpdate,
    method: 'preUpdate',
    entity: Project::class
)]
final class ProjectsListener
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

        if ($project->getStatus() === ProjectStatus::InCampaign) {
            $calendar = $this->calendarService->makeCalendar($project->getDeadline());

            $project->setCalendar($calendar);
        }
    }
}
