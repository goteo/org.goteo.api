<?php

namespace App\ApiResource\User;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\QueryParameter;
use App\ApiResource\Accounting\AccountingApiResource;
use App\Dto\UserSignupDto;
use App\Entity\User\User;
use App\Entity\User\UserType;
use App\Filter\OrderedLikeFilter;
use App\Mapping\Transformer\UserDisplayNameMapTransformer;
use App\State\ApiResourceStateProvider;
use App\State\User\UserSignupProcessor;
use App\State\User\UserStateProcessor;
use AutoMapper\Attribute\MapFrom;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Users represent people who interact with the platform.
 */
#[API\ApiResource(
    shortName: 'User',
    stateOptions: new Options(entityClass: User::class),
    provider: ApiResourceStateProvider::class,
    processor: UserStateProcessor::class,
    parameters: new Parameters([
        'email' => new QueryParameter(
            security: 'is_granted("ROLE_ADMIN")',
            description: 'Only available to admin users'
        ),
    ])
)]
#[API\GetCollection()]
#[API\Post(input: UserSignupDto::class, processor: UserSignupProcessor::class)]
#[API\Get()]
#[API\Patch(securityPostDenormalize: 'is_granted("USER_EDIT", previous_object)')]
#[API\Delete(securityPostDenormalize: 'is_granted("USER_EDIT", previous_object)')]
class UserApiResource
{
    #[API\ApiProperty(writable: false, identifier: true)]
    public int $id;

    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[API\ApiFilter(SearchFilter::class, strategy: 'partial')]
    #[API\ApiProperty(security: 'is_granted("USER_EDIT", object)')]
    public string $email;

    /**
     * A unique, non white space, byte-safe string identifier for this User.
     */
    #[Assert\NotBlank()]
    #[Assert\Length(min: 4, max: 30)]
    #[Assert\Regex('/^[a-z0-9_]+$/')]
    #[API\ApiFilter(filterClass: OrderedLikeFilter::class)]
    public string $handle;

    /**
     * URL to the avatar image of this User.
     */
    #[Assert\Url()]
    public string $avatar;

    /**
     * Is this User for an individual acting on their own or a group of individuals?
     */
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("USER_EDIT", previous_object)')]
    public UserType $type;

    /**
     * A list of the roles assigned to this User. Admin scoped property.
     *
     * @var array<int, string>
     */
    #[API\ApiProperty(
        security: 'is_granted("ROLE_ADMIN")',
        securityPostDenormalize: 'is_granted("ROLE_ADMIN")'
    )]
    public array $roles;

    #[API\ApiProperty(writable: false)]
    #[MapFrom(User::class, transformer: UserDisplayNameMapTransformer::class)]
    public string $displayName;

    /**
     * For `individual` User types: personal data about the User themselves.\
     * For `organization` User types: data for the organization representative or person managing the User.
     */
    #[API\ApiProperty(writable: false)]
    public PersonApiResource $person;

    /**
     * For `organization` User types only. Legal entity data.
     */
    #[API\ApiProperty(writable: false)]
    public ?OrganizationApiResource $organization = null;

    /**
     * The Accounting for this User monetary movements.
     */
    #[API\ApiProperty(writable: false)]
    #[API\ApiFilter(SearchFilter::class, strategy: 'exact')]
    public AccountingApiResource $accounting;

    /**
     * The Projects that are owned by this User.
     *
     * @var array<int, \App\ApiResource\Project\ProjectApiResource>
     */
    #[API\ApiProperty(writable: false)]
    public array $projects;

    /**
     * Has this User confirmed their email address?
     */
    #[API\ApiProperty(writable: false, security: 'is_granted("USER_VIEW", object)')]
    public bool $emailConfirmed;

    /**
     * A flag determined by the platform for Users who are known to be active.
     */
    #[API\ApiProperty(writable: false)]
    public bool $active;
}
