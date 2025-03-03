<?php

namespace App\Entity\User;

enum UserType: string
{
    /**
     * People who sign-up on their own and do not represent any other legal or natural person.
     */
    case Individual = 'individual';

    /**
     * Sometimes organizations want to sign-up and represent themselves.
     */
    case Organization = 'organization';
}
