<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Model\Response\Response;
use Dullahan\User\Port\Application\Manager\UserPersistManagerInterface;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;
use Dullahan\User\Port\Domain\UserValidationServiceInterface;
use Dullahan\User\Presentation\Event\Transport\Saga\RemovalSaga;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ManageSagaListener
{
    public function __construct(
        private UserRetrieveServiceInterface $userService,
        private UserValidationServiceInterface $userValidateService,
        private UserPersistManagerInterface $userPersistManager,
    ) {
    }

    #[AsEventListener(event: RemovalSaga::class)]
    public function removal(RemovalSaga $event): void
    {
        $parameters = $event->request->getBodyParameters();
        $user = $this->userService->getLoggedInUser();
        $this->userValidateService->validateUserRemoval($user, $parameters['user']['password'] ?? '');

        $wasRemoved = $this->userPersistManager->remove(
            (int) $user->getId(),
            (bool) ($parameters['user']['deleteAll'] ?? false)
        );
        if (!$wasRemoved) {
            throw new \Exception('User was not removed during RemoveUser event chain', 500);
        }

        $event->setResponse(new Response('User removed successfully'));
    }
}
