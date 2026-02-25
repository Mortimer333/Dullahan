<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application\Manager;

interface UserStatusManagerInterface
{
    public function activate(int $id, #[\SensitiveParameter] string $token): void;

    public function deactivate(int $id): void;
}
