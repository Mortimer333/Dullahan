<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Model\Body\Manage;

use Dullahan\User\Presentation\Http\Definition\Manage\UpdateDTO;
use OpenApi\Attributes as SWG;

class UpdateUserDTO
{
    #[SWG\Property]
    public UpdateDTO $update;
}
