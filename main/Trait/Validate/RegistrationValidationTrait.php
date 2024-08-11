<?php

declare(strict_types=1);

namespace Dullahan\Main\Trait\Validate;

use Dullahan\Main\Constraint\RegistrationConstraint;
use Dullahan\Main\Entity\User;
use Dullahan\Main\Entity\UserData;

trait RegistrationValidationTrait
{
    /**
     * @param array<string, mixed> $registration
     */
    public function validateRegistration(array $registration): void
    {
        $this->validate($registration, RegistrationConstraint::get());
        if ($this->httpUtilService->hasErrors()) {
            throw new \Exception('Registration failed', 400);
        }
    }

    public function validateUserUniqueness(string $email, string $name): void
    {
        $this->validateEmailUniqueness($email);
        $this->validateUsernameUniqueness($name);
    }

    public function validateEmailUniqueness(string $email, ?User $existingUser = null): void
    {
        $userWithEmail = $this->em->getRepository(User::class)->findUniqueEmail($email);
        if (!is_null($existingUser) && !is_null($userWithEmail) && $userWithEmail === $existingUser) {
            return;
        }

        if (!is_null($userWithEmail) && $userWithEmail->getEmail() === $email) {
            $this->httpUtilService->addError('User with this e-mail already exists', ['email']);
        } elseif (!is_null($userWithEmail) && $userWithEmail->getNewEmail() === $email) {
            $this->httpUtilService->addError('Someone is changing their email to the one you\'ve chosen', ['email']);
        }
    }

    public function validateUsernameUniqueness(string $name, ?User $existingUser = null): void
    {
        $userDataWithName = $this->em->getRepository(UserData::class)->findOneBy(['name' => $name]);
        if (!is_null($existingUser) && !is_null($userDataWithName) && $userDataWithName === $existingUser->getData()) {
            return;
        }

        if (!is_null($userDataWithName)) {
            $this->httpUtilService->addError('User with this name already exists', ['username']);
        }
    }

    public function validateUserPassword(string $password, string $repeated): void
    {
        if ($password !== $repeated) {
            $this->httpUtilService->addError("Passwords don't match", ['passwordRepeat']);
        }
    }
}
