<?php

namespace App\Service\Scout;

use Embed\Embed;
use Embed\Http\Crawler;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

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

    private function normalizeUrl(string $url)
    {
        $nurl = $url;

        if (!\parse_url($url, \PHP_URL_SCHEME)) {
            $nurl = \sprintf('https://%s', $url);
        }

        return $nurl;
    }

    /**
     * @param string $url A URL to an external resource
     *
     * @throws \Exception When the given $url string could not be validated as an actual URL
     */
    public function get(string $url): ScoutResult
    {
        $isValidUrl = Validation::createIsValidCallable(null, new Url(), new NotBlank());
        if (!$isValidUrl($url)) {
            throw new \Exception(\sprintf("Value '%s' could not be validated as an URL", $url));
        }

        $info = $this->embed->get($this->normalizeUrl($url));

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

            if (isset($result->retry) && $result->retry !== null) {
                return $this->get($result->retry);
            }
        }

        return $result;
    }
}
