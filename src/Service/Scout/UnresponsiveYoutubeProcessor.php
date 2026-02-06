<?php

namespace App\Service\Scout;

/**
 * Tries to fix bad YouTube responses caused by malformed URI host.
 */
class UnresponsiveYoutubeProcessor implements ScoutProcessorInterface
{
    public function supports(ScoutResult $result): bool
    {
        return $result->image === null && \in_array($result->getUri()->getHost(), [
            'youtube.com',
        ]);
    }

    public function process(ScoutResult $result): ScoutResult
    {
        $uri = $result->getUri();
        $result->retry = $uri->withHost(\sprintf('www.%s', $uri->getHost()));

        return $result;
    }
}
