<?php

namespace App\Service\Embed;

use Embera\Embera;

class EmbedService
{
    private Embera $embera;

    public function __construct(
        iterable $providerFilters,
    ) {
        $embera = new Embera();

        /** @var EmbedFilterInterface[] */
        $filters = \iterator_to_array($providerFilters);
        foreach ($filters as $filter) {
            $embera->addFilter(function ($data) use ($filter) {
                if (!$filter::supports(\rtrim($data['provider_url'], '/'))) {
                    return $data;
                }

                return $filter::filter($data);
            });
        }

        $this->embera = $embera;
    }

    /**
     * @param string $url A URL to the a video resource
     *
     * @throws \Exception When the URL does not contain any embedable data
     */
    public function getVideo(string $url): EmbedVideo
    {
        $url = $this->parseUrl($url);
        $data = $this->embera->getUrlData($url);

        if (empty($data)) {
            throw new \Exception(\sprintf('Could not extract embed data from %s', $url));
        }

        $data = $data[$url];

        return new EmbedVideo(
            $this->getIframeSrc($data['html']),
            \trim($data['thumbnail_url']),
        );
    }

    private function parseUrl(string $url): string
    {
        $urlData = \parse_url($url);

        if (!\array_key_exists('path', $urlData) || empty($urlData['path'])) {
            return '';
        }

        if (!\array_key_exists('scheme', $urlData)) {
            return $this->parseUrl(\sprintf('https://%s', $url));
        }

        $query = '';
        if (\array_key_exists('query', $urlData)) {
            $query = \sprintf('?%s', $urlData['query']);
        }

        return \sprintf(
            '%s://%s%s%s',
            $urlData['scheme'],
            $urlData['host'],
            $urlData['path'],
            $query
        );
    }

    private function getIframeSrc(string $iframe): string
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($iframe);

        return $dom
            ->getElementsByTagName('body')
            ->item(0)->childNodes
            ->item(0)->getAttribute('src');
    }
}
