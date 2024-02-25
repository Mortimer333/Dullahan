<?php

namespace Dullahan\Model\Body\Authentication;

use Dullahan\Model\Definition\Authenticate\ResetPasswordVerifyDTO;
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

    #[SWG\Property(
        example: '03AFY_a8URlKX2b....gLHkKDHYrsaG3RsJJByHAnraVVZkk2w',
        description: 'reCaptcha client-side generated token'
    )]
    public string $recaptcha;
}
