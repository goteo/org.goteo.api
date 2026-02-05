<?php

namespace App\Service\Scout;

use Embed\Embed;
use Embed\Http\Crawler;
use Psr\Http\Client\ClientInterface;

class ScoutService
{
    private Embed $embed;

    /**
     * @param iterable<ScoutProcessorInterface> $processors
     */
    public function __construct(
        private iterable $processors,
        private ClientInterface $httpClient,
    ) {
        $embed = new Embed(new Crawler($httpClient));

        $this->embed = $embed;
    }

    /**
     * @param string $url A URL to the a video resource
     */
    public function get(string $url): ScoutResult
    {
        $info = $this->embed->get($url);

        $result = new ScoutResult(
            $info->getUri(),
            $info->getRequest(),
            $info->getResponse(),
            $info->getCrawler()
        );

        foreach ($this->processors as $processor) {
            if (!$processor->supports($result)) {
                continue;
            }

            $result = $processor->process($result);
        }

        return $result;
    }
}
