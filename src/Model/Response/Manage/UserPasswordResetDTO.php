<?php

declare(strict_types=1);

namespace Dullahan\Model\Response\Manage;

use Dullahan\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class UserPasswordResetDTO extends SuccessDTO
{
    #[SWG\Property(
        example: 'Password reset has finished successfully',
        description: 'Description of the successful request'
    )]
    public string $message;
}
