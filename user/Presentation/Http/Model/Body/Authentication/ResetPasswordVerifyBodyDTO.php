<?php

namespace Dullahan\User\Presentation\Http\Model\Body\Authentication;

use Dullahan\User\Presentation\Http\Definition\Authenticate\ResetPasswordVerifyDTO;
use OpenApi\Attributes as SWG;

class ResetPasswordVerifyBodyDTO
{
    #[SWG\Property]
    public ResetPasswordVerifyDTO $forgotten;
}
