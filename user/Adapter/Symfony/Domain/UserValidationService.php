<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Domain;

use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Main\Symfony\SymfonyConstraintValidationService;
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

class UserValidationService extends SymfonyConstraintValidationService implements UserValidationServiceInterface
{
    public function __construct(
        protected UserVerifyAndSetServiceInterface $userVerifyAndSetService,
        protected ValidatorInterface $validator,
        protected HttpUtilService $httpUtilService,
        protected RegistrationValidationServiceInterface $registrationValidationService,
        protected ErrorCollectorInterface $errorCollector,
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
        if ($this->errorCollector->hasErrors()) {
            throw new \Exception('Updating your details has failed', 400);
        }
    }

    /**
     * @param array<string, mixed> $update
     */
    public function validateUpdateUserMail(array $update, User $user): void
    {
        $this->validate($update, UserUpdateMailConstraint::get());
        if ($this->errorCollector->hasErrors()) {
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
        if ($this->errorCollector->hasErrors()) {
            throw new \Exception('Updating your password has failed', 400);
        }

        /** @var string $password */
        $password = $update['oldPassword'] ?? throw new \Exception('Missing password', 500);
        if (!$this->userVerifyAndSetService->verifyUserPassword($password, $user)) {
            throw new \Exception("Sent password doesn't match, password was not changed", 403);
        }

        /** @var string $newPassword */
        $newPassword = $update['newPassword'] ?? throw new \Exception('Missing new password', 500);
        $errors = $this->validatePasswordStrength($newPassword, length: 12);
        foreach ($errors as $error) {
            $this->errorCollector->addError($error, ['newPassword']);
        }
        if ($this->errorCollector->hasErrors()) { // @phpstan-ignore-line
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
        if ($this->errorCollector->hasErrors()) {
            throw new \Exception('Resetting your password has failed', 400);
        }
    }

    /**
     * @param array{forgotten: array<string, mixed>} $forgotten
     */
    public function validateResetPassword(#[\SensitiveParameter] array $forgotten): bool
    {
        $this->validate($forgotten, ResetPasswordConstraint::get());
        if ($this->errorCollector->hasErrors()) {
            return false;
        }
        /** @var string $password */
        $password = $forgotten['forgotten']['password'] ?? throw new \Exception('Missing new password', 500);
        $errors = $this->validatePasswordStrength($password, length: 12);
        foreach ($errors as $error) {
            $this->errorCollector->addError($error, ['forgotten', 'password']);
        }
        if ($this->errorCollector->hasErrors()) {
            return false;
        }

        /** @var string $passwordRepeat */
        $passwordRepeat = $forgotten['forgotten']['passwordRepeat'] ?? throw new \Exception('Missing repeated new password', 500);
        if ($passwordRepeat != $password) {
            $this->errorCollector->addError("Repeated password doesn't match new password", ['forgotten', 'passwordRepeat']);

            return false;
        }

        return true;
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
    ): array {
        $errors = [];
        if (mb_strlen($password) < $length) {
            array_push($errors, "Password is too short, it is required to have $length characters");
        }

        if ($upper && !preg_match('@[A-Z]@', $password)) {
            array_push($errors, 'Password is required to have uppercase characters');
        }

        if ($lower && !preg_match('@[a-z]@', $password)) {
            array_push($errors, 'Password is required to have lowercase characters');
        }

        if ($number && !preg_match('@[0-9]@', $password)) {
            array_push($errors, 'Password is required to have numeric characters');
        }

        if ($special && !preg_match('@[^\w]@', $password)) {
            array_push($errors, 'Password is required to have special characters');
        }

        return $errors;
    }
}
