<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Definition\Authenticate;

use OpenApi\Attributes as SWG;

class ResetPasswordVerifyDTO
{
    #[SWG\Property(example: 'passwordBIG1@', description: 'New password')]
    public string $password;

    #[SWG\Property(example: 'passwordBIG1@', description: 'new repeated password')]
    public string $passwordRepeat;
}
