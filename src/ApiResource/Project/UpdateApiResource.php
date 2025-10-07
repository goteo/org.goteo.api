<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\LocalizedApiResourceTrait;
use App\ApiResource\TimestampedCreationApiResource;
use App\ApiResource\TimestampedUpdationApiResource;
use App\Entity\Project\Update;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A ProjectUpdate offers records of significant news during a Project's life.\
 * \
 * Updates can be for outstanding donation amounts, achievement of goals or thresholds,
 * or any other body of information that the Project owner(s) may wish to make public to the Project's audience.
 */
#[API\ApiResource(
    shortName: 'ProjectUpdate',
    stateOptions: new Options(entityClass: Update::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class,
    securityPostDenormalize: 'is_granted("PROJECT_EDIT", object.project)',
    securityPostDenormalizeMessage: 'You do not have permission to add Updates to that Project'
)]
class UpdateApiResource
{
    use LocalizedApiResourceTrait;
    use TimestampedCreationApiResource;
    use TimestampedUpdationApiResource;

    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The Project to which this update belongs to.
     */
    #[Assert\NotBlank()]
    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public ProjectApiResource $project;

    /**
     * Main headline for this update.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Secondary headline for this update.
     */
    #[Assert\NotBlank()]
    public string $subtitle;

    /**
     * Main text body of the Project's update.
     */
    #[Assert\NotBlank()]
    public string $body;

    /**
     * Public display date for this update,
     * not necessarily related to the actual dates of resource creation or update.
     */
    #[Assert\DateTime()]
    #[API\ApiProperty(default: 'now')]
    #[API\ApiFilter(OrderFilter::class)]
    public \DateTimeInterface $date;

    /**
     * URL to an image resource to be displayed as header.
     */
    #[Assert\Url()]
    public string $cover;
}
