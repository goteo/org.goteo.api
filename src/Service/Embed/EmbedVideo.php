<?php

namespace App\Service\Embed;

class EmbedVideo
{
    public function __construct(
        /**
         * A URL path to the embedded video element source.
         */
        public readonly string $src,

        /**
         * A URL path to the embedded video element thumbnail.
         */
        public readonly ?string $thumbnail = null,
    ) {}
}
