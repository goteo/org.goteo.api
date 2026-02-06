<?php

namespace App\Service\Scout;

use Embed\Extractor;
use Psr\Http\Message\UriInterface;

class ScoutResult extends Extractor
{
    /**
     * Large-size thumbnail image.
     */
    public ?UriInterface $cover;

    /**
     * If a Processor decides a Result failed, it shall provide a retry URI.
     * The ScoutService will scout the retry target instead.
     */
    public ?UriInterface $retry;
}
