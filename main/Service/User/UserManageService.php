<?php

declare(strict_types=1);

namespace Dullahan\Main\Service\User;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Contract\Marker\UserServiceInterface;
use Dullahan\Main\Entity\User;
use Dullahan\Main\Entity\UserData;
use Dullahan\Main\Service\Util\BinUtilService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManageService
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordHasher,
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService,
        protected BinUtilService $binUtilService,
    ) {
    }

    /**
     * @param array<string, string> $registration
     */
    public function create(#[\SensitiveParameter] array $registration): User
    {
        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $registration['password']
        );

        $userData = new UserData();
        $userData->setName($registration['username'])
            ->setPublicId('')
        ;

        $user->setEmail($registration['email'])
            ->setPassword($hashedPassword)
            ->setData($userData)
        ;

        $this->setActivationToken($user);

        $this->em->persist($user);
        $this->em->flush();

        $userData->setPublicId($this->binUtilService->generateUniqueToken((string) $user->getId()));

        $this->em->persist($userData);
        $this->em->flush();

        return $user;
    }

    public function remove(int $id, bool $deleteAll = false): void
    {
        /** @var User $user */
        $user = $this->userService->get($id);
        /** @var UserData $data */
        $data = $user->getData();
        if ($deleteAll) {
            $this->em->remove($data);
        } else {
            $data->setUser(null)
                ->setOldName($data->getName())      // saving old name to display it together with deletion date
                ->setName(null)                     // setting name to null to make it available for other users to use
                ->setDeleted(time())
            ;
            $this->em->persist($data);
        }
        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): void
    {
        $userData = $user->getData();
        if (!$userData) {
            throw new \Exception('User data got detached, contact administrator', 500);
        }

        $fields = [
            'username' => 'setName',
            'sendNewsletter' => 'setSendNewsletter',
        ];

        foreach ($fields as $key => $setter) {
            if (!isset($data[$key])) {
                continue;
            }

            $userData->$setter($data[$key]);
        }

        $this->em->persist($userData);
        $this->em->flush();
    }

    public function updateNewEmail(User $user, ?string $email): void
    {
        $user->setNewEmail($email);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function updateNewPassword(User $user, #[\SensitiveParameter] ?string $password): void
    {
        if (is_null($password)) {
            $user->setNewPassword(null);
        } else {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setNewPassword($hashedPassword);
        }
        $this->em->persist($user);
        $this->em->flush();
    }

    public function resetPassword(User $user, #[\SensitiveParameter] string $password): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $user->setPasswordResetVerificationTokenExp(null);
        $user->setPasswordResetVerificationToken(null);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function setActivationToken(User $user): void
    {
        $user->setActivationToken($this->binUtilService->generateToken(32));
        // TODO make expiry time a parameter in config
        $user->setActivationTokenExp(time() + (60 * 60 * 24)); // Token expires after 24 hours
    }
}
