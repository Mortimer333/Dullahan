<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Model\Body\Manage;

use Dullahan\User\Presentation\Http\Definition\Manage\UpdateEmailDTO;
use OpenApi\Attributes as SWG;

class UpdateUserEmailDTO
{
    #[SWG\Property]
    public UpdateEmailDTO $update;
}
