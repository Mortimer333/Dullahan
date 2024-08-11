<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Definition\Manage;

use OpenApi\Attributes as SWG;

class UpdateEmailDTO
{
    #[SWG\Property(example: 'mail@mail.com', description: 'Email')]
    public string $email;

    #[SWG\Property(example: 'password1@BIG', description: 'Password to verify with logged user')]
    public string $password;
}
