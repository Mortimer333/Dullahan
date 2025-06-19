<?php

namespace Dullahan\User\Presentation\Http\Model\Body\Authentication;

use Dullahan\User\Presentation\Http\Definition\Authenticate\ResetPasswordDTO;
use OpenApi\Attributes as SWG;

class ResetPasswordBodyDTO
{
    #[SWG\Property]
    public ResetPasswordDTO $forgotten;
}
