<?php

declare(strict_types=1);

namespace Dullahan\Model\Body\Manage;

use Dullahan\Model\Definition\Manage\UpdatePasswordDTO;
use OpenApi\Attributes as SWG;

class UpdateUserPasswordDTO
{
    #[SWG\Property]
    public UpdatePasswordDTO $update;
}
