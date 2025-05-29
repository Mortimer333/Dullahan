<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Port\Application\UserServiceInterface;
use Dullahan\User\Port\Domain\UserVerifyAndSetServiceInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserVerifyAndSetService implements UserVerifyAndSetServiceInterface
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordHasher,
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService,
    ) {
    }

    public function verifyUserRemoval(User $user, #[\SensitiveParameter] string $password): void
    {
        if (!$this->verifyUserPassword($password, $user)) {
            throw new \Exception("Sent password doesn't match, user was not removed", 403);
        }
    }

    public function verifyUserPassword(#[\SensitiveParameter] string $password, User $user): bool
    {
        if (!class_implements($user)[PasswordAuthenticatedUserInterface::class]) {
            return false;
        }

        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function verifyNewEmail(int $userId, #[\SensitiveParameter] string $token): void
    {
        $user = $this->userService->get($userId);
        if (empty($user->getEmailVerificationToken())) {
            throw new \Exception('No change request was created', 400);
        }

        if ($user->getEmailVerificationTokenExp() < time()) {
            throw new \Exception('Token has expired', 400);
        }

        if ($user->getEmailVerificationToken() !== $token) {
            throw new \Exception("Token didn't match", 403);
        }

        if (null === $user->getNewEmail()) {
            throw new \Exception('There is no new email to set', 500);
        }

        $user->setEmail($user->getNewEmail());
        $user->setNewEmail(null);
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExp(null);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function verifyNewPassword(int $userId, #[\SensitiveParameter] string $token): void
    {
        $user = $this->userService->get($userId);
        if (empty($user->getPasswordVerificationToken())) {
            throw new \Exception('No change request was created', 400);
        }

        if ($user->getPasswordVerificationTokenExp() < time()) {
            throw new \Exception('Token has expired', 400);
        }

        if ($user->getPasswordVerificationToken() !== $token) {
            throw new \Exception("Token didn't match", 403);
        }

        // Just double-checking if we are not setting empty password by mistake
        if (empty($user->getNewPassword())) {
            throw new \Exception('New password is empty', 500);
        }

        $user->setPassword($user->getNewPassword());
        $user->setNewPassword(null);
        $user->setPasswordVerificationToken(null);
        $user->setPasswordVerificationTokenExp(null);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function verifyResetPasswordToken(#[\SensitiveParameter] string $token): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['passwordResetVerificationToken' => $token]);
        if (!$user) {
            throw new \Exception('Invalid token', 403);
        }

        if ($user->getPasswordResetVerificationTokenExp() < time()) {
            throw new \Exception('Token has expired', 400);
        }

        return $user;
    }
}
