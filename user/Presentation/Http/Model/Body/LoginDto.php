<?php

namespace Dullahan\User\Presentation\Http\Model\Body;

use OpenApi\Attributes as SWG;

class LoginDto
{
    #[SWG\Property(example: 'mail@mail.com', description: 'Unique e-mail')]
    public string $username;

    #[SWG\Property(
        example: 'password1@BIG',
        description: 'Password of length at least 12, one big letter,' .
            ' one small letter, one number and one special character'
    )]
    public string $password;
}
