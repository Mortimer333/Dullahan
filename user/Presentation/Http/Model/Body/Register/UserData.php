<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Model\Body\Register;

use OpenApi\Attributes as SWG;

class UserData
{
    #[SWG\Property(example: 'mail@mail.com', description: 'Unique e-mail')]
    public string $email;

    #[SWG\Property(example: 'Username', description: 'Unique user name')]
    public string $username;

    #[SWG\Property(
        example: 'password1@BIG',
        description: 'Password of length at least 12, one big letter,' .
        ' one small letter, one number and one special character'
    )]
    public string $password;

    #[SWG\Property(
        example: 'password1@BIG',
        description: 'Repeated password'
    )]
    public string $passwordRepeat;
}
