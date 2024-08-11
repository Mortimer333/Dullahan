<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Body\Manage;

use Dullahan\Main\Model\Definition\Manage\UpdateEmailDTO;
use OpenApi\Attributes as SWG;

class UpdateUserEmailDTO
{
    #[SWG\Property]
    public UpdateEmailDTO $update;
}
