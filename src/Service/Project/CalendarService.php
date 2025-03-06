<?php

namespace App\Service\Project;

use App\Entity\Project\ProjectCalendar;
use App\Entity\Project\ProjectDeadline;

class CalendarService
{
    public function makeCalendar(
        ProjectDeadline $deadline,
        \DateTimeImmutable $release = new \DateTimeImmutable(),
        int $minimumTtl = 40,
        int $optimumTtl = 40,
    ): ProjectCalendar {
        $minimumTtl = \DateInterval::createFromDateString(\sprintf('%d days', $minimumTtl));
        $optimumTtl = \DateInterval::createFromDateString(\sprintf('%d days', $optimumTtl));

        $calendar = new ProjectCalendar();
        $calendar->release = $release;

        switch ($deadline) {
            case ProjectDeadline::Minimum:
                $calendar->minimum = $calendar->release->add($minimumTtl);
                break;
            case ProjectDeadline::Optimum:
                $calendar->minimum = $calendar->release->add($minimumTtl);
                $calendar->optimum = $calendar->minimum->add($optimumTtl);
                break;
        }

        return $calendar;
    }
}
