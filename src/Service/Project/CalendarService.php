<?php

namespace App\Service\Project;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectCalendar;
use App\Entity\Project\ProjectDeadline;

class CalendarService
{
    public function makeCalendar(
        Project $project,
        int $minimumTtl = 40,
        int $optimumTtl = 40,
    ): ProjectCalendar {
        $release = new \DateTimeImmutable();
        $minimumTtl = \DateInterval::createFromDateString(\sprintf('%d days', $minimumTtl));
        $optimumTtl = \DateInterval::createFromDateString(\sprintf('%d days', $optimumTtl));

        $calendar = new ProjectCalendar();
        $calendar->release = $release;

        switch ($project->getDeadline()) {
            case ProjectDeadline::Minimum:
                $calendar->minimum = $release->add($minimumTtl);
                break;
            case ProjectDeadline::Optimum:
                $calendar->minimum = $release->add($minimumTtl);
                $calendar->optimum = $calendar->minimum->add($optimumTtl);
                break;
        }

        return $calendar;
    }
}
