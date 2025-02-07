<?php

namespace App\Mapping\Transformer;

use App\Entity\Project\ProjectVideo;
use App\Service\Embed\EmbedService;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class ProjectVideoMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private EmbedService $embedService,
    ) {}

    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        $video = $this->embedService->getVideo($value);

        return new ProjectVideo($video->src, $video->thumbnail);
    }
}
