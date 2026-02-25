<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Application;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Main\Service\Util\FileUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Domain\Exception\UserNotFoundException;
use Dullahan\User\Domain\Exception\UserNotLoggedInException;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserRetrieveService implements UserRetrieveServiceInterface
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
            throw new UserNotFoundException('Cannot find selected user');
        }

        return $user;
    }

    public function getLoggedInUser(): User
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if (!$user) {
            throw new UserNotLoggedInException('User is not logged in');
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
}
