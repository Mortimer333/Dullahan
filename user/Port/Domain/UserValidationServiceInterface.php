<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Domain;

use Dullahan\User\Domain\Entity\User;

interface UserValidationServiceInterface
{
    public function validateUserRemoval(User $user, #[\SensitiveParameter] string $password): void;

    /**
     * @param array<string, mixed> $update
     */
    public function validateUpdateUser(array $update): void;

    /**
     * @param array<string, mixed> $update
     */
    public function validateUpdateUserMail(array $update, User $user): void;

    /**
     * @param array<string, mixed> $update
     */
    public function validatePasswordChange(#[\SensitiveParameter] array $update, User $user): void;

    /**
     * @param array<string, mixed> $forgotten
     */
    public function validateForgottenPassword(#[\SensitiveParameter] array $forgotten): void;

    /**
     * @param array<string, mixed> $forgotten
     */
    public function validateResetPassword(#[\SensitiveParameter] array $forgotten): void;

    public function validatePasswordStrength(
        string $password,
        bool $upper = true,
        bool $lower = true,
        bool $number = true,
        bool $special = true,
        int $length = 8,
    ): bool;
}
