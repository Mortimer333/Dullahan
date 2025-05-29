<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Definition\Manage;

use OpenApi\Attributes as SWG;

class UpdateDTO
{
    #[SWG\Property(example: 'Username', description: 'Username')]
    public string $username;

    #[SWG\Property(example: false, description: 'Should system send newsletter emails to user')]
    public bool $sendNewsletter;
}
