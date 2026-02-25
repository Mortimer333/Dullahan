<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application;

use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\ValueObject\UserBaseline;

interface UserPersistServiceInterface
{
    public function create(UserBaseline $user): User;

    public function remove(int $id, bool $deleteAll = false): void;

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): void;

    public function updateNewEmail(User $user, ?string $email): void;

    public function updateNewPassword(User $user, #[\SensitiveParameter] ?string $password): void;

    public function resetPassword(User $user, #[\SensitiveParameter] string $password): void;

    public function setActivationToken(User $user): void;

    public function activate(int $id, #[\SensitiveParameter] string $token): void;

    public function deactivate(int $id): void;
}
