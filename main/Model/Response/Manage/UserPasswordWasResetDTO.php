<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Response\Manage;

use Dullahan\Main\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class UserPasswordWasResetDTO extends SuccessDTO
{
    #[SWG\Property(
        example: 'Password reset was reset successfully',
        description: 'Description of the successful request'
    )]
    public string $message;
}
