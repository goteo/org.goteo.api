<?php

namespace App\Mapping\Transformer;

use App\Entity\Project\ProjectVideo;
use App\Service\Scout\ScoutService;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class ProjectVideoMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private ScoutService $scoutService,
    ) {}

    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        $info = $this->scoutService->get($value);

        return new ProjectVideo($info->src, $info->cover, $info->image);
    }
}
