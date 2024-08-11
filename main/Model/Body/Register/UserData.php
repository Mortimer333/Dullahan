<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Body\Register;

use OpenApi\Attributes as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class UserData
{
    #[SWG\Property(example: 'mail@mail.com', description: 'Unique e-mail')]
    #[Assert\NotBlank()]
    public string $email;

    #[SWG\Property(example: 'Username', description: 'Unique user name')]
    #[Assert\NotBlank()]
    public string $username;

    #[Assert\NotBlank()]
    #[SWG\Property(
        example: 'password1@BIG',
        description: 'Password of length at least 12, one big letter,' .
        ' one small letter, one number and one special character'
    )]
    public string $password;

    #[Assert\NotBlank()]
    #[SWG\Property(
        example: 'password1@BIG',
        description: 'Repeated password'
    )]
    public string $passwordRepeat;
}
