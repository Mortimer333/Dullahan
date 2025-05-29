<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Model\Body\Manage;

use Dullahan\User\Presentation\Http\Definition\Manage\UpdatePasswordDTO;
use OpenApi\Attributes as SWG;

class UpdateUserPasswordDTO
{
    #[SWG\Property]
    public UpdatePasswordDTO $update;
}
