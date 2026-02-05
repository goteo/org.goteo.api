<?php

namespace App\Service\Scout;

use Embed\Embed;
use Embed\Http\Crawler;
use Embed\Http\CurlClient;

class ScoutService
{
    private Embed $embed;

    /**
     * @param iterable<ScoutProcessorInterface> $processors
     */
    public function __construct(
        private iterable $processors,
    ) {
        $client = new CurlClient();
        $client->setSettings([
            'timeout' => 2,
            'max_redirs' => 3,
            'user_agent' => 'goteo/v4',
        ]);

        $embed = new Embed(new Crawler($client));

        $this->embed = $embed;
    }

    /**
     * @param string $url A URL to the a video resource
     *
     * @throws \Exception When the URL does not contain any embedable data
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
