<?php

namespace App\Service\Embed;

use Embera\Embera;

class EmbedService
{
    private Embera $embera;

    /**
     * @param iterable $providerFilters
     */
    public function __construct(
        iterable $providerFilters
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

    public function getData(string $url): ?EmbedData
    {
        $data = $this->embera->getUrlData($url);

        if (empty($data)) {
            return null;
        }

        $data = $data[$url];

        return new EmbedData(
            $this->getIframeSrc($data['html']),
            \trim($data['thumbnail_url']),
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
