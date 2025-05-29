<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Domain;

use Dullahan\User\Domain\Entity\User;

interface RegistrationValidationServiceInterface
{
    /**
     * @param array<string, mixed> $registration
     */
    public function validateRegistration(array $registration): void;
    public function validateUserUniqueness(string $email, string $name): void;
    public function validateEmailUniqueness(string $email, ?User $existingUser = null): void;
    public function validateUsernameUniqueness(string $name, ?User $existingUser = null): void;
    public function validateUserPassword(string $password, string $repeated): void;
}
