<?php

namespace App\Entity\Project;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable()]
class ProjectCalendar
{
    /**
     * The date at which the Project started campaigning.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTimeInterface $release = null;

    /**
     * 40 days after the date of release.\
     * \
     * The minimum budget must be raised by the end of this date.\
     * Failure to do so will move the Project out of status `in_campaign` into status `unfunded`.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTimeInterface $minimum = null;

    /**
     * 40 days after the minimum deadline. Optional.\
     * \
     * If the Project achieved their minimum budget by the minimum deadline,
     * and this deadline is defined, it may still remain in campaign to raise the optimum.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTimeInterface $optimum = null;
}
