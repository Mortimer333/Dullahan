<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Domain;

use Dullahan\User\Domain\Entity\User;

interface UserVerifyAndSetServiceInterface
{
    public function verifyUserRemoval(User $user, #[\SensitiveParameter] string $password): void;

    public function verifyUserPassword(#[\SensitiveParameter] string $password, User $user): bool;

    public function verifyNewEmail(int $userId, #[\SensitiveParameter] string $token): void;

    public function verifyNewPassword(int $userId, #[\SensitiveParameter] string $token): void;

    public function verifyResetPasswordToken(#[\SensitiveParameter] string $token): User;
}
