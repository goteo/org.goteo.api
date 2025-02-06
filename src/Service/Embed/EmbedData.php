<?php

namespace App\Service\Embed;

class EmbedData
{
    public function __construct(
        public readonly string $videoUrl,
        public readonly string $thumbnailUrl,
    ) {}
}
