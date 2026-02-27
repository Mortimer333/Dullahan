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

    #[SWG\Property(
        example: '90946d99cd23844d8994574d5990d7b92afe0590f50775ca0c8da9f1b7e5586e',
        description: 'Reset password token'
    )]
    public string $token;
}
