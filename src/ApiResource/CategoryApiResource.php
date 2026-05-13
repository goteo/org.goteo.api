<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\Category;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;

/**
 * A Category can be used by other resources as a "topic intent".\
 * For example. Projects might relate with up to 2 Categories, which are used by the Project
 * as a way to describe itself and can be used to discover similar Projects.\
 * \
 * Categories can only be modified by users with the role "ROLE_ADMIN", but can usually
 * be referenced by non-admin users in their own resources, such as Project owners.
 */
#[API\ApiResource(
    shortName: 'Category',
    stateOptions: new Options(entityClass: Category::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class,
)]
#[API\GetCollection()]
#[API\Post(security: 'is_granted("ROLE_ADMIN")')]
#[API\Get()]
#[API\Patch(security: 'is_granted("ROLE_ADMIN")')]
#[API\Delete(security: 'is_granted("ROLE_ADMIN")')]
class CategoryApiResource
{
    use LocalizedApiResourceTrait;

    #[API\ApiProperty(identifier: true)]
    public string $id;

    #[API\ApiProperty()]
    public string $name;
}
