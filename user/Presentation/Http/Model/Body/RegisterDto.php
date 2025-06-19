<?php

namespace Dullahan\User\Presentation\Http\Model\Body;

use Dullahan\User\Presentation\Http\Model\Body\Register\UserData;
use OpenApi\Attributes as SWG;

class RegisterDto
{
    #[SWG\Property()]
    public UserData $register;
}
