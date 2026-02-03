<?php

namespace App\Service\Embed;

use Embed\Embed;
use Embed\Http\Crawler;
use Embed\Http\CurlClient;

class EmbedService
{
    private Embed $embed;

    /**
     * @param iterable<UriProcessorInterface> $uriProcessors
     */
    public function __construct(
        private iterable $uriProcessors,
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
    public function getVideo(string $url): EmbedVideo
    {
        $info = $this->embed->get($url);
        $image = $info->image;

        if (!$image) {
            throw new \Exception("Could not obtain an image for the video $url");
        }

        foreach ($this->uriProcessors as $uriProcessor) {
            if (!$uriProcessor->supports($image)) {
                continue;
            }

            $image = $uriProcessor->process($image);
        }

        return new EmbedVideo($info->url, $image);
    }
}
