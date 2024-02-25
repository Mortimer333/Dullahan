<?php

declare(strict_types=1);

namespace Dullahan\Model\Body\Manage;

use Dullahan\Model\Definition\Manage\RemoveDTO;
use OpenApi\Attributes as SWG;

class RemoveUserDTO
{
    #[SWG\Property]
    public RemoveDTO $user;
}
