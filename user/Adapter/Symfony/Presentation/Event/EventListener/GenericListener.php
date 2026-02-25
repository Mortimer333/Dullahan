<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\User\Adapter\Symfony\Infrastructure\Repository\UserDataRepository;
use Dullahan\User\Adapter\Symfony\Infrastructure\Repository\UserRepository;
use Dullahan\User\Port\Application\UserPersistServiceInterface;
use Dullahan\User\Presentation\Event\Transport\Flush;
use Dullahan\User\Presentation\Event\Transport\Registration\CreateUser;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class GenericListener
{
    public function __construct(
        private UserPersistServiceInterface $userManageService,
        private UserRepository $userRepository,
        private UserDataRepository $userDataRepository,
        private BinUtilService $binUtilService,
    ) {
    }

    #[AsEventListener(event: CreateUser::class)]
    public function userCreation(CreateUser $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setUser($this->userManageService->create($event->getUserBaseline()));
    }

    #[AsEventListener(event: Flush::class)]
    public function flush(Flush $event): void
    {
        match ($event->context->getProperty(Flush::FLUSH_PURPOSE)) {
            Flush::REGISTER => $this->flushRegister($event),
            default => null,
        };
    }

    private function flushRegister(Flush $event): void
    {
        $user = $event->user;
        $userData = $user->getData();
        try {
            if (!$userData) {
                throw new \InvalidArgumentException('Missing user data during registration', 500);
            }

            $this->userRepository->save($user, true);
            $userData->setPublicId($this->binUtilService->generateUniqueToken((string) $user->getId()));
            $this->userDataRepository->save($userData, true);
        } catch (\Throwable $e) {
            if ($userData?->getId()) {
                $this->userDataRepository->remove($userData, true);
            }

            if ($user->getId()) {
                $this->userRepository->remove($user, true);
            }

            throw $e;
        }
    }
}
