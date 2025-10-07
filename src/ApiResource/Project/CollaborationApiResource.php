<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\LocalizedApiResourceTrait;
use App\ApiResource\TimestampedCreationApiResource;
use App\ApiResource\TimestampedUpdationApiResource;
use App\Entity\Project\Collaboration;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProjectCollaborations are used as requests for help on specific problems that the Project might face and cannot be budgeted-in.
 */
#[API\ApiResource(
    shortName: 'ProjectCollaboration',
    stateOptions: new Options(entityClass: Collaboration::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class,
    securityPostDenormalize: 'is_granted("PROJECT_EDIT", object.project)',
    securityPostDenormalizeMessage: 'You do not have permission to add collaborations to that Project'
)]
class CollaborationApiResource
{
    use LocalizedApiResourceTrait;
    use TimestampedCreationApiResource;
    use TimestampedUpdationApiResource;

    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The Project which requests this collaboration.
     */
    #[Assert\NotBlank()]
    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public ProjectApiResource $project;

    /**
     * A short, descriptive title for this collaboration.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Information about this collaboration. More detailed than the title.
     */
    #[Assert\NotBlank()]
    public string $description;

    /**
     * Wether or not the collaboration's problem has been solved.
     */
    #[Assert\NotNull()]
    #[Assert\Type('bool')]
    #[API\ApiFilter(BooleanFilter::class)]
    public bool $isFulfilled = false;
}
