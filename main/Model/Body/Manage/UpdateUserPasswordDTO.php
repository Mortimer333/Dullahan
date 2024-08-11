<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Body\Manage;

use Dullahan\Main\Model\Definition\Manage\UpdatePasswordDTO;
use OpenApi\Attributes as SWG;

class UpdateUserPasswordDTO
{
    #[SWG\Property]
    public UpdatePasswordDTO $update;
}
