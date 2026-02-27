<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application\Manager;

interface UserStatusManagerInterface
{
    //    public function activate(int $id): void;
    //
    //    public function deactivate(int $id): void;
    //
    //    public function canActivate(int $id, #[\SensitiveParameter] string $token): bool;

    public function canResetPassword(int $id, #[\SensitiveParameter] string $token): bool;
}
