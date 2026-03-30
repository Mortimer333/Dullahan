<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Application;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Exception\UserNotFoundException;
use Dullahan\User\Domain\Exception\UserNotLoggedInException;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;
use Dullahan\User\Presentation\Event\Transport\SerializeUser;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @phpstan-import-type SerializedUser from \Dullahan\User\Port\Application\UserRetrieveServiceInterface
 */
class UserRetrieveService implements UserRetrieveServiceInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Security $security,
        private EventDispatcherInterface $eventDispatcher,
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

    public function serialize(User $user): array
    {
        /** @var SerializedUser $serialized */
        $serialized = $this->eventDispatcher->dispatch(new SerializeUser($user))->getSerialized();

        return $serialized;
    }

    public function getByEmail(string $email): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);
        if (!$user) {
            throw new UserNotFoundException('Cannot find selected user');
        }

        return $user;
    }
}
