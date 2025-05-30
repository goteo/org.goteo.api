<?php

namespace App\Service\Embed;

use Embera\Cache\Filesystem;
use Embera\Embera;
use Embera\Http\HttpClient;
use Embera\Http\HttpClientCache;
use Embera\ProviderCollection\DefaultProviderCollection;

class EmbedService
{
    public const HTTP_CACHE_TTL = 86400 * 2;

    public const HTTP_CACHE_DIR = 'embera';

    private Embera $embera;

    public function __construct(
        string $cacheDir,
        iterable $providerFilters,
        iterable $providerFactories,
    ) {
        $embera = new Embera(
            config: $this->buildConfig($providerFactories),
            collection: $this->buildCollection($providerFactories),
            httpClient: $this->buildHttpCache($cacheDir)
        );

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
     * @param iterable<Provider\EmbedProviderFactoryInterface> $providerFactories
     */
    private function buildConfig(iterable $providerFactories)
    {
        $config = [];

        foreach ($providerFactories as $factory) {
            $config = [...$config, ...$factory->getConfig()];
        }

        return $config;
    }

    /**
     * @param iterable<Provider\EmbedProviderFactoryInterface> $providerFactories
     */
    private function buildCollection(iterable $providerFactories)
    {
        $defaultCollection = new DefaultProviderCollection();

        foreach ($providerFactories as $factory) {
            $providers = $factory->createProviders();

            foreach ($providers as $host => $provider) {
                $defaultCollection->addProvider($host, $provider);
            }
        }

        return $defaultCollection;
    }

    private function buildHttpCache(string $rootCacheDir)
    {
        $emberaCacheDir = \sprintf(
            '%s%s%s',
            $rootCacheDir,
            \DIRECTORY_SEPARATOR,
            self::HTTP_CACHE_DIR
        );

        $httpCache = new HttpClientCache(new HttpClient());
        $httpCache->setCachingEngine(new Filesystem($emberaCacheDir, self::HTTP_CACHE_TTL));

        return $httpCache;
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
            \array_key_exists('thumbnail_url', $data) ? \trim($data['thumbnail_url']) : null
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
