<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\Category;
use App\State\ApiResourceStateProvider;

#[API\ApiResource(
    shortName: 'Category',
    stateOptions: new Options(entityClass: Category::class)
)]
#[API\GetCollection(provider: ApiResourceStateProvider::class)]
#[API\Get(provider: ApiResourceStateProvider::class)]
class CategoryApiResource
{
    #[API\ApiProperty(identifier: true)]
    public string $id;
}
