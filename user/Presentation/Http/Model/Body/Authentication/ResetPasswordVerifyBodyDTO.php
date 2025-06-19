<?php

namespace Dullahan\User\Presentation\Http\Model\Body\Authentication;

use Dullahan\User\Presentation\Http\Definition\Authenticate\ResetPasswordVerifyDTO;
use OpenApi\Attributes as SWG;

class ResetPasswordVerifyBodyDTO
{
    #[SWG\Property]
    public ResetPasswordVerifyDTO $forgotten;

    #[SWG\Property(
        example: '90946d99cd23844d8994574d5990d7b92afe0590f50775ca0c8da9f1b7e5586e',
        description: 'Reset password token'
    )]
    public string $token;
}
