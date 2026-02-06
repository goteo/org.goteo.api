<?php

namespace App\Service\Scout;

/**
 * Transforms a YouTube thumbnail URI into a high-res URI for a cover.
 */
class YoutubeCoverProcessor implements ScoutProcessorInterface
{
    public function supports(ScoutResult $result): bool
    {
        return $result->image !== null && \in_array($result->image->getHost(), [
            'youtube.com',
            'i.ytimg.com',
        ]);
    }

    public function process(ScoutResult $result): ScoutResult
    {
        $highResPath = \preg_replace('/\w+.jpg$/', 'maxresdefault.jpg', $result->image->getPath());

        $result->cover = $result->image->withPath($highResPath);

        return $result;
    }
}
