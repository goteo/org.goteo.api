<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\TimestampedCreationApiResource;
use App\ApiResource\TimestampedUpdationApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Project\ReviewComment;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;

/**
 * ProjectReviewComments hold the conversation between the reviewer and the reviewed Project owner.
 */
#[API\ApiResource(
    shortName: 'ProjectReviewComment',
    stateOptions: new Options(entityClass: ReviewComment::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class,
)]
class ReviewCommentApiResource
{
    use TimestampedCreationApiResource;
    use TimestampedUpdationApiResource;

    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public ReviewAreaApiResource $area;

    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public UserApiResource $author;

    public string $body;
}
