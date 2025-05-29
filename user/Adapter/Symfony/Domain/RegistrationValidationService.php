<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Main\Trait\Validate\SymfonyValidationHelperTrait;
use Dullahan\User\Adapter\Symfony\Presentation\Http\Constraint\RegistrationConstraint;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Port\Domain\RegistrationValidationServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationValidationService implements RegistrationValidationServiceInterface
{
    use SymfonyValidationHelperTrait;

    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $em,
    ) {
    }

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
