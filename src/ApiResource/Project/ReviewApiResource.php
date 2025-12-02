<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\TimestampedCreationApiResource;
use App\ApiResource\TimestampedUpdationApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Project\Review;
use App\Entity\Project\ReviewType;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;

/**
 * ProjectReviews are launched as health-checks for Projects.\
 * \
 * The reviews are focused on certain aspects of a Project's lifecycle. The types `campaign` and `financial` reviews
 * are to evaluate the fitness of a Project before being greenlit for campaigning or the legitimacy of their raised funds, respectively.
 */
#[API\ApiResource(
    shortName: 'ProjectReview',
    stateOptions: new Options(entityClass: Review::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
class ReviewApiResource
{
    use TimestampedCreationApiResource;
    use TimestampedUpdationApiResource;

    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public ProjectApiResource $project;

    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public UserApiResource $reviewer;

    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public ReviewType $type;

    /**
     * @var ReviewAreaApiResource[]
     */
    public array $areas;
}
