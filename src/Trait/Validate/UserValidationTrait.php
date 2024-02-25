<?php

declare(strict_types=1);

namespace Dullahan\Trait\Validate;

use Dullahan\Entity\User;
use Dullahan\src\Constraint\ForgottenPasswordConstraint;
use Dullahan\src\Constraint\ResetPasswordConstraint;
use Dullahan\src\Constraint\UserUpdateConstraint;
use Dullahan\src\Constraint\UserUpdateMailConstraint;
use Dullahan\src\Constraint\UserUpdatePasswordConstraint;

trait UserValidationTrait
{
    /**
     * @param array<string, mixed> $update
     */
    public function validateUpdateUser(array $update): void
    {
        $this->validate($update, UserUpdateConstraint::get());
        if ($this->httpUtilService->hasErrors()) {
            throw new \Exception('Updating your details has failed', 400);
        }
    }

    /**
     * @param array<string, mixed> $update
     */
    public function validateUpdateUserMail(array $update, User $user): void
    {
        $this->validate($update, UserUpdateMailConstraint::get());
        if ($this->httpUtilService->hasErrors()) {
            throw new \Exception('Updating your email has failed', 400);
        }

        /** @var string $password */
        $password = $update['password'] ?? throw new \Exception('Missing password', 500);
        if (!$this->userValidateService->verifyUserPassword($password, $user)) {
            throw new \Exception("Sent password doesn't match, user email was not updated", 403);
        }

        /** @var string $email */
        $email = $update['email'] ?? throw new \Exception('Missing email', 500);
        if ($email === $user->getEmail()) {
            throw new \Exception('New email cannot be the same as old one', 400);
        }
        $this->validateEmailUniqueness($email);
    }

    /**
     * @param array<string, mixed> $update
     */
    public function validatePasswordChange(#[\SensitiveParameter] array $update, User $user): void
    {
        $this->validate($update, UserUpdatePasswordConstraint::get());
        if ($this->httpUtilService->hasErrors()) {
            throw new \Exception('Updating your password has failed', 400);
        }

        /** @var string $password */
        $password = $update['oldPassword'] ?? throw new \Exception('Missing password', 500);
        if (!$this->userValidateService->verifyUserPassword($password, $user)) {
            throw new \Exception("Sent password doesn't match, password was not changed", 403);
        }

        /** @var string $newPassword */
        $newPassword = $update['newPassword'] ?? throw new \Exception('Missing new password', 500);
        $this->validatePasswordStrength($newPassword, length: 12);
        if ($this->httpUtilService->hasErrors()) { // @phpstan-ignore-line
            throw new \Exception('Changing your password has failed', 400);
        }

        /** @var string $newPasswordRepeat */
        $newPasswordRepeat = $update['newPasswordRepeat'] ?? throw new \Exception('Missing repeated new password', 500);
        if ($newPasswordRepeat != $newPassword) {
            throw new \Exception("Repeated password doesn't match new password", 400);
        }
    }

    /**
     * @param array<string, mixed> $forgotten
     */
    public function validateForgottenPassword(#[\SensitiveParameter] array $forgotten): void
    {
        $this->validate($forgotten, ForgottenPasswordConstraint::get());
        if ($this->httpUtilService->hasErrors()) {
            throw new \Exception('Resetting your password has failed', 400);
        }
    }

    /**
     * @param array<string, mixed> $forgotten
     */
    public function validateResetPassword(#[\SensitiveParameter] array $forgotten): void
    {
        $this->validate($forgotten, ResetPasswordConstraint::get());
        if ($this->httpUtilService->hasErrors()) {
            throw new \Exception('Resetting your password has failed', 400);
        }

        /** @var string $password */
        $password = $forgotten['password'] ?? throw new \Exception('Missing new password', 500);
        $this->validatePasswordStrength($password, length: 12);
        if ($this->httpUtilService->hasErrors()) { // @phpstan-ignore-line
            throw new \Exception('Changing your password has failed', 400);
        }

        /** @var string $passwordRepeat */
        $passwordRepeat = $forgotten['passwordRepeat'] ?? throw new \Exception('Missing repeated new password', 500);
        if ($passwordRepeat != $password) {
            throw new \Exception("Repeated password doesn't match new password", 400);
        }
    }
}
