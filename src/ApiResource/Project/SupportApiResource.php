<?php

namespace App\ApiResource\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Gateway\ChargeApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Project\Support;
use App\State\ApiResourceStateProvider;
use App\State\Project\SupportStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[API\ApiResource(
    shortName: 'ProjectSupport',
    stateOptions: new Options(entityClass: Support::class),
    provider: ApiResourceStateProvider::class,
    processor: SupportStateProcessor::class,
)]
#[API\GetCollection()]
#[API\Get()]
#[API\Post(security: 'is_granted("ROLE_USER")')]
#[API\Patch(security: 'is_granted("SUPPORT_EDIT", previous_object)')]
#[API\Delete(security: 'is_granted("SUPPORT_EDIT", previous_object)')]
class SupportApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The User who created the ProjectSupport record.
     */
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public UserApiResource $owner;

    /**
     * The Project being targeted in the Charges.
     */
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(filterClass: SearchFilter::class, strategy: 'exact')]
    public ProjectApiResource $project;

    /**
     * The Charges that were paid by the User,
     * all charges must go to the same Project and
     * can't be on an existing ProjectSupport record.
     * 
     * @var array<int, ChargeApiResource>
     */
    #[Assert\NotBlank()]
    public array $charges;

    /**
     * A message of support from the User to the Project.
     */
    public ?string $message = null;

    /**
     * User's will to have their support to the Project be shown publicly.
     */
    public bool $anonymous = true;
}
