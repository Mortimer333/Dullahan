<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Application;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Domain\ValueObject\UserBaseline;
use Dullahan\User\Port\Application\UserPersistServiceInterface;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPersistService implements UserPersistServiceInterface
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordHasher,
        protected EntityManagerInterface $em,
        protected UserRetrieveServiceInterface $userRetrieveService,
        protected BinUtilService $binUtilService,
    ) {
    }

    public function create(UserBaseline $userDTO): User
    {
        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $userDTO->password,
        );

        $userData = new UserData();
        $userData->setName($userDTO->username)
            ->setPublicId('')
        ;

        $user->setEmail($userDTO->email)
            ->setPassword($hashedPassword)
            ->setData($userData)
        ;

        $this->enableActivation($user);

        return $user;
    }

    public function remove(int $id, bool $deleteAll = false): void
    {
        /** @var User $user */
        $user = $this->userRetrieveService->get($id);
        /** @var UserData $data */
        $data = $user->getData();
        if ($deleteAll) {
            $this->em->remove($data);
        } else {
            $data->setUser(null)
                ->setOldName($data->getName())      // saving old name to display it together with deletion date
                ->setName(null)                     // setting name to null to make it available for other users
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
            'sendNewsletter' => 'setSendNewsletter', // @TODO what is this doing here???
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

    public function enablePasswordReset(User $user): void
    {
        $user->setPasswordResetVerificationToken($this->binUtilService->generateToken(32));
        // TODO make expiry time a parameter in config
        $user->setPasswordResetVerificationTokenExp(time() + (60 * 60 * 24)); // Token expires after 24 hours
    }

    public function enableActivation(User $user): void
    {
        $user->setActivationToken($this->binUtilService->generateToken(32));
        // TODO make expiry time a parameter in config
        $user->setActivationTokenExp(time() + (60 * 60 * 24)); // Token expires after 24 hours
    }

    public function activate(int $id, #[\SensitiveParameter] string $token): void
    {
        $user = $this->userRetrieveService->get($id);
        if ($user->isActivated()) {
            throw new \Exception('User is already activated', 400);
        }

        if (!empty($user->getActivationToken()) && $user->getActivationToken() !== $token) {
            throw new \Exception("Account wasn't activated", 403);
        }

        $user->setWhenActivated(time());
        $user->setActivationToken(null);
        $user->setActivated(true);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function deactivate(int $id): void
    {
        $user = $this->userRetrieveService->get($id);
        if (!$user->isActivated()) {
            throw new \Exception('User is already deactivated', 400);
        }

        $user->setActivationToken(null);
        $user->setActivated(false);
        $this->em->persist($user);
        $this->em->flush();
    }
}
