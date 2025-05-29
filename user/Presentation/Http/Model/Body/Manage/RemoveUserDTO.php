<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Model\Body\Manage;

use Dullahan\User\Presentation\Http\Definition\Manage\RemoveDTO;
use OpenApi\Attributes as SWG;

class RemoveUserDTO
{
    #[SWG\Property]
    public RemoveDTO $user;
}
