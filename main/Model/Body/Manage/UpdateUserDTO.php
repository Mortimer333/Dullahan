<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Body\Manage;

use Dullahan\Main\Model\Definition\Manage\UpdateDTO;
use OpenApi\Attributes as SWG;

class UpdateUserDTO
{
    #[SWG\Property]
    public UpdateDTO $update;
}
