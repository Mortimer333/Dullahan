<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application\Manager;

interface UserActionManagerInterface
{
    public function enableEmailChange(int $id, string $email): void;
    public function finishEmailChange(int $id, #[\SensitiveParameter] string $token): void;

    //    public function updateNewPassword(User $user, #[\SensitiveParameter] ?string $password): void;

    public function resetPassword(int $id, #[\SensitiveParameter] string $password): void;

    public function enablePasswordReset(string $email): void;
}
