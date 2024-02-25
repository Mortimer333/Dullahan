<?php

declare(strict_types=1);

namespace Dullahan\Model\Body\Manage;

use Dullahan\Model\Definition\Manage\UpdateEmailDTO;
use OpenApi\Attributes as SWG;

class UpdateUserEmailDTO
{
    #[SWG\Property]
    public UpdateEmailDTO $update;
}
