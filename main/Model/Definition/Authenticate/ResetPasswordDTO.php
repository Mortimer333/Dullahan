<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Definition\Authenticate;

use OpenApi\Attributes as SWG;

class ResetPasswordDTO
{
    #[SWG\Property(example: 'mail@mail.com', description: 'Email associated with user')]
    public string $mail;
}
