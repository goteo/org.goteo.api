<?php

namespace App\Service\Embed;

use Embed\Embed;
use Embed\Http\Crawler;
use Embed\Http\CurlClient;

class EmbedService
{
    private Embed $embed;

    public function __construct()
    {
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

        return new EmbedVideo($info->url, $info->image);
    }
}
