<?php

namespace App\Service\Scout;

use Embed\Extractor;
use Psr\Http\Message\UriInterface;

class ScoutResult extends Extractor
{
    public ?UriInterface $cover;
}
