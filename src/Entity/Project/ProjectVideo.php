<?php

namespace App\Entity\Project;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ProjectVideo
{
    public function __construct(
        #[ORM\Column(type: Types::STRING, nullable: true)]
        public readonly string $src = '',
        #[ORM\Column(type: Types::STRING, nullable: true)]
        public readonly ?string $cover = null,
        #[ORM\Column(type: Types::STRING, nullable: true)]
        public readonly ?string $thumbnail = null,
    ) {}
}
