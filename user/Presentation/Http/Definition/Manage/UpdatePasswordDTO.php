<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Definition\Manage;

use OpenApi\Attributes as SWG;

class UpdatePasswordDTO
{
    #[SWG\Property(example: 'passwordBIG1@', description: 'Old password')]
    public string $oldPassword;

    #[SWG\Property(example: 'passwordBIG1@', description: 'New password')]
    public string $newPassword;

    #[SWG\Property(example: 'passwordBIG1@', description: 'new repeated password')]
    public string $newPasswordRepeat;
}
