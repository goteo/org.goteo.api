<?php

namespace App\Service\Scout;

/**
 * Tries to fix bad YouTube responses caused by malformed URI host.
 */
class YoutubeBadhostProcessor implements ScoutProcessorInterface
{
    public function supports(ScoutResult $result): bool
    {
        return $result->title === null && \in_array($result->getUri()->getHost(), [
            'youtube.com',
        ]);
    }

    public function process(ScoutResult $result): ScoutResult
    {
        $uri = $result->getUri();
        $wwwHost = \sprintf('www.%s', $uri->getHost());

        $result->retry = $uri->withHost($wwwHost);

        return $result;
    }
}
