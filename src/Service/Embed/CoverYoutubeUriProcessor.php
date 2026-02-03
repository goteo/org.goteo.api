<?php

namespace App\Service\Embed;

use Psr\Http\Message\UriInterface;

class CoverYoutubeUriProcessor implements UriProcessorInterface
{
    public function supports(UriInterface $uri): bool
    {
        return \in_array($uri->getHost(), [
            'youtube.com',
            'i.ytimg.com',
        ]);
    }

    public function process(UriInterface $uri): UriInterface
    {
        $highResPath = \preg_replace('/\w+.jpg$/', 'maxresdefault.jpg', $uri->getPath());

        return $uri->withPath($highResPath);
    }
}
