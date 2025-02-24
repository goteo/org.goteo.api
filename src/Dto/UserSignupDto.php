<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UserSignupDto
{
    /**
     * A valid e-mail address for the new User.
     */
    #[Assert\NotBlank()]
    #[Assert\Email()]
    public string $email;

    /**
     * The auth password for the new User. Plaintext string,
     * will be hashed by the API.
     */
    #[Assert\NotBlank()]
    #[Assert\Length(min: 8)]
    public string $password;
}
