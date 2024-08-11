<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Body\Manage;

use Dullahan\Main\Model\Definition\Manage\RemoveDTO;
use OpenApi\Attributes as SWG;

class RemoveUserDTO
{
    #[SWG\Property]
    public RemoveDTO $user;
}
