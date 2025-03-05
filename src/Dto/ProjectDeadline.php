<?php

namespace App\Dto;

enum ProjectDeadline: string
{
    /**
     * A single deadline means the Project will only remain in campaing
     * for the minimum budget deadline.
     */
    case Minimum = 'minimum';

    /**
     * A double deadline means the Project can remain in campaing
     * past the minimum budget deadline until the optimum budget deadline,
     * if it did raise the minimum in the first deadline.
     */
    case Optimum = 'optimum';
}
