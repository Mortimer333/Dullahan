<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Domain;

use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Main\Trait\Validate\SymfonyValidationHelperTrait;
use Dullahan\User\Adapter\Symfony\Presentation\Http\Constraint\ForgottenPasswordConstraint;
use Dullahan\User\Adapter\Symfony\Presentation\Http\Constraint\ResetPasswordConstraint;
use Dullahan\User\Adapter\Symfony\Presentation\Http\Constraint\UserUpdateConstraint;
use Dullahan\User\Adapter\Symfony\Presentation\Http\Constraint\UserUpdateMailConstraint;
use Dullahan\User\Adapter\Symfony\Presentation\Http\Constraint\UserUpdatePasswordConstraint;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Port\Domain\RegistrationValidationServiceInterface;
use Dullahan\User\Port\Domain\UserValidationServiceInterface;
use Dullahan\User\Port\Domain\UserVerifyAndSetServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserValidationService implements UserValidationServiceInterface
{
    use SymfonyValidationHelperTrait;

    public function __construct(
        protected UserVerifyAndSetServiceInterface $userVerifyAndSetService,
        protected ValidatorInterface $validator,
        protected HttpUtilService $httpUtilService,
        protected RegistrationValidationServiceInterface $registrationValidationService,
    ) {
    }

    public function validateUserRemoval(User $user, #[\SensitiveParameter] string $password): void
    {
        if (!$this->userVerifyAndSetService->verifyUserPassword($password, $user)) {
            throw new \Exception("Sent password doesn't match, user was not removed", 403);
        }
    }

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
        if (!$this->userVerifyAndSetService->verifyUserPassword($password, $user)) {
            throw new \Exception("Sent password doesn't match, user email was not updated", 403);
        }

        /** @var string $email */
        $email = $update['email'] ?? throw new \Exception('Missing email', 500);
        if ($email === $user->getEmail()) {
            throw new \Exception('New email cannot be the same as old one', 400);
        }
        $this->registrationValidationService->validateEmailUniqueness($email);
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
        if (!$this->userVerifyAndSetService->verifyUserPassword($password, $user)) {
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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) - the main idea is to have a lot of different outcomes/flexibility
     */
    public function validatePasswordStrength(
        string $password,
        bool $upper = true,
        bool $lower = true,
        bool $number = true,
        bool $special = true,
        int $length = 8,
    ): bool {
        $valid = true;
        if (mb_strlen($password) < $length) {
            $valid = false;
            $this->httpUtilService->addError("Password is too short, it is required to have $length characters");
        }

        if ($upper && !preg_match('@[A-Z]@', $password)) {
            $valid = false;
            $this->httpUtilService->addError('Password is required to have uppercase characters');
        }

        if ($lower && !preg_match('@[a-z]@', $password)) {
            $valid = false;
            $this->httpUtilService->addError('Password is required to have lowercase characters');
        }

        if ($number && !preg_match('@[0-9]@', $password)) {
            $valid = false;
            $this->httpUtilService->addError('Password is required to have numeric characters');
        }

        if ($special && !preg_match('@[^\w]@', $password)) {
            $valid = false;
            $this->httpUtilService->addError('Password is required to have special characters');
        }

        return $valid;
    }
}
