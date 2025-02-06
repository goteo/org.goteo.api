<?php

namespace App\Service\Embed;

class VimeoEmbedFilter implements EmbedFilterInterface
{
    public static function supports(string $providerUrl): bool
    {
        return \str_ends_with($providerUrl, 'vimeo.com');
    }

    public static function filter(array $data): array
    {
        $data['thumbnail_url'] = \preg_replace('/_\d+x\d+$/', '_900x500', $data['thumbnail_url']);

        return $data;
    }
}
