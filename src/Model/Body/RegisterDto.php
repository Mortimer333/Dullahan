<?php

namespace Dullahan\Model\Body;

use Dullahan\Model\Body\Register\UserData;
use OpenApi\Attributes as SWG;

class RegisterDto
{
    #[SWG\Property()]
    public UserData $register;

    #[SWG\Property(
        example: '03AFY_a8URlKX2b....gLHkKDHYrsaG3RsJJByHAnraVVZkk2w',
        description: 'reCaptcha client-side generated token'
    )]
    public string $recaptcha;
}
