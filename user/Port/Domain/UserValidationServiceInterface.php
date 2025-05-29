<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Domain;

use Dullahan\User\Domain\Entity\User;

interface UserValidationServiceInterface
{
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
}
