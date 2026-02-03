<?php

namespace App\Service\Embed;

use Psr\Http\Message\UriInterface;

interface UriProcessorInterface
{
    /**
     * Decide if the URI is processable.
     */
    public function supports(UriInterface $uri): bool;

    /**
     * Process the input URI.
     *
     * @param UriInterface $uri The input URI
     *
     * @return UriInterface The output URI
     */
    public function process(UriInterface $uri): UriInterface;
}
