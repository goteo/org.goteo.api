<?php

namespace App\Entity\Project;

use App\Service\Embed\EmbedVideo;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ProjectVideo extends EmbedVideo
{
    public function __construct(
        #[ORM\Column(type: Types::STRING, nullable: true)]
        public readonly string $src = '',
        #[ORM\Column(type: Types::STRING, nullable: true)]
        public readonly ?string $thumbnail = null,
    ) {}
}
