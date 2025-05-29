<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Response\Manage;

use Dullahan\Main\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class UserPasswordUpdatedDTO extends SuccessDTO
{
    #[SWG\Property(
        example: 'User password verified successful',
        description: 'Description of the successful request'
    )]
    public string $message;
}
