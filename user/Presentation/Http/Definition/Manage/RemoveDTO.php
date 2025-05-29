<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Definition\Manage;

use OpenApi\Attributes as SWG;

class RemoveDTO
{
    #[SWG\Property(example: 'password1@BIG', description: 'User password to verify authenticity')]
    public string $password;

    #[SWG\Property(example: false, description: 'Choose if all (not private data) should be deleted with user')]
    public string $deleteAll;
}
