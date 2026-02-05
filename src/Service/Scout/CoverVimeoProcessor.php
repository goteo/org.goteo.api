<?php

namespace App\Service\Scout;

/**
 * Transforms a Vimeo thumbnail URI into an HD URI for a cover.
 */
class CoverVimeoProcessor implements ScoutProcessorInterface
{
    public function supports(ScoutResult $result): bool
    {
        return \in_array($result->image->getHost(), [
            'vimeo.com',
            'i.vimeocdn.com',
        ]);
    }

    public function process(ScoutResult $result): ScoutResult
    {
        $highResPath = \preg_replace('/_\d+x\d+$/', '_1280x720', $result->image->getPath());

        $result->cover = $result->image->withPath($highResPath);

        return $result;
    }
}
