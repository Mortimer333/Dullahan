<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Application;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Main\Service\Util\FileUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Port\Application\UserServiceInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserService implements UserServiceInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Security $security,
    ) {
    }

    public function get(int $id): User
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            throw new \Exception('Cannot find selected user', 400);
        }

        return $user;
    }

    public function getLoggedInUser(): User
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if (!$user) {
            throw new \Exception('User is not logged in', 400);
        }

        return $this->get((int) $user->getId());
    }

    public function isLoggedIn(): bool
    {
        return (bool) $this->security->getUser();
    }

    // @TODO add event for serialization and move asset specific information to it
    public function serialize(User $user): array
    {
        /** @var UserData $data */
        $data = $user->getData();
        $currentTakenSpace = $this->em->getRepository(Asset::class)->getTakenSpace($data);

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'data' => $this->serializeData($data),
            'activated' => (bool) $user->isActivated(),
            'created' => date('Y-m-d H:i:s', $user->getCreated()),
            'storage' => [
                'readable' => [
                    'limit' => FileUtilService::humanFilesize((int) $data->getFileLimitBytes()),
                    'taken' => FileUtilService::humanFilesize($currentTakenSpace),
                ],
                'limit' => (int) $data->getFileLimitBytes(),
                'taken' => $currentTakenSpace,
            ],
        ];
    }

    /**
     * @return array{id: int|null, name: string|null}
     */
    public function serializeData(UserData $data): array
    {
        return [
            'id' => $data->getId(),
            'name' => $data->getName(),
        ];
    }

    public function activate(int $id, #[\SensitiveParameter] string $token): void
    {
        $user = $this->get($id);
        if ($user->isActivated()) {
            throw new \Exception('User is already activated', 400);
        }

        if (!empty($user->getActivationToken()) && $user->getActivationToken() !== $token) {
            throw new \Exception("Account wasn't activated", 400);
        }

        $user->setWhenActivated(time());
        $user->setActivationToken(null);
        $user->setActivated(true);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function deactivate(int $id): void
    {
        $user = $this->get($id);
        if (!$user->isActivated()) {
            throw new \Exception('User is already deactivated', 400);
        }

        $user->setActivationToken(null);
        $user->setActivated(false);
        $this->em->persist($user);
        $this->em->flush();
    }
}
