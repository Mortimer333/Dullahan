<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Response\Manage;

use Dullahan\Main\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class UserEmailVerificationSendDTO extends SuccessDTO
{
    #[SWG\Property(
        example: 'User verification email was sent successfully',
        description: 'Description of the successful request'
    )]
    public string $message;
}
