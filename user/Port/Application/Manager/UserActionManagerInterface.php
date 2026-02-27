<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application\Manager;

use Dullahan\User\Domain\Entity\User;

interface UserActionManagerInterface
{
    //    public function updateNewEmail(User $user, ?string $email): void;
    //
    //    public function updateNewPassword(User $user, #[\SensitiveParameter] ?string $password): void;
    //
    //    public function resetPassword(User $user, #[\SensitiveParameter] string $password): void;

    public function enablePasswordReset(string $email): void;
}
