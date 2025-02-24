<?php

namespace App\ApiResource\User;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\User\Person;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;

/**
 * Most times a real person is behind a User account,
 * we keep their data separated from the User record to allow for other User types,
 * like the ones owned by an organization, to exist all at the same level.\
 * \
 * All Person records are only visible to their respective owners and platform admins.
 * Sensitive personal data is encrypted before being stored in the database.
 */
#[API\ApiResource(
    shortName: 'Person',
    stateOptions: new Options(entityClass: Person::class),
    security: 'is_granted("PERSON_VIEW", object)',
    securityPostDenormalize: 'is_granted("PERSON_EDIT", object)',
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
#[API\Get()]
#[API\Patch()]
class PersonApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public UserApiResource $user;

    /**
     * Personal ID for tax purposes. e.g: NIF, Steuernummer, SSN, etc.
     */
    #[API\ApiProperty(
        security: 'is_granted("PERSON_VIEW", object)',
        securityPostDenormalize: 'is_granted("PERSON_EDIT", object)',
    )]
    public string $taxId;

    /**
     * First-part of the name of the person,
     * in most western conventions this is the given name(s). e.g: John, Juan, etc.
     */
    #[API\ApiProperty(
        security: 'is_granted("PERSON_VIEW", object)',
        securityPostDenormalize: 'is_granted("PERSON_EDIT", object)',
    )]
    public string $firstName;

    /**
     * Last-part of the name of the person,
     * in most western conventions this is the family name(s). e.g: Smith, Herrera García, etc.
     */
    #[API\ApiProperty(
        security: 'is_granted("PERSON_VIEW", object)',
        securityPostDenormalize: 'is_granted("PERSON_EDIT", object)',
    )]
    public string $lastName;
}
