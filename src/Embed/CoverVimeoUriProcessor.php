<?php

namespace App\Embed;

use Psr\Http\Message\UriInterface;

/**
 * Transforms a Vime thumbnail URI into an HD URI for a cover.
 */
class CoverVimeoUriProcessor implements UriProcessorInterface
{
    public function supports(UriInterface $uri): bool
    {
        return \in_array($uri->getHost(), [
            'vimeo.com',
            'i.vimeocdn.com',
        ]);
    }

    public function process(UriInterface $uri): UriInterface
    {
        $highResPath = \preg_replace('/_\d+x\d+$/', '_1280x720', $uri->getPath());

        return $uri->withPath($highResPath);
    }
}
