<?php

namespace Dullahan\Model\Body\Authentication;

use Dullahan\Model\Definition\Authenticate\ResetPasswordDTO;
use OpenApi\Attributes as SWG;

class ResetPasswordBodyDTO
{
    #[SWG\Property]
    public ResetPasswordDTO $forgotten;

    #[SWG\Property(
        example: '03AFY_a8URlKX2b....gLHkKDHYrsaG3RsJJByHAnraVVZkk2w',
        description: 'reCaptcha client-side generated token'
    )]
    public string $recaptcha;
}
