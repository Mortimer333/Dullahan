<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\Main\Model\Response\Response;
use Dullahan\User\Port\Application\Manager\UserActionManagerInterface;
use Dullahan\User\Port\Application\Manager\UserPersistManagerInterface;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;
use Dullahan\User\Port\Domain\UserValidationServiceInterface;
use Dullahan\User\Presentation\Event\Transport\Saga\RemovalSaga;
use Dullahan\User\Presentation\Event\Transport\Saga\UpdateEmailSaga;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ManageSagaListener
{
    public function __construct(
        private UserRetrieveServiceInterface $userService,
        private UserValidationServiceInterface $userValidateService,
        private UserPersistManagerInterface $userPersistManager,
        private UserActionManagerInterface $userActionManager,
        private ErrorCollectorInterface $errorCollector,
    ) {
    }

    #[AsEventListener(event: RemovalSaga::class)]
    public function removal(RemovalSaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

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

    #[AsEventListener(event: UpdateEmailSaga::class)]
    public function onEmailUpdate(UpdateEmailSaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $user = $this->userService->getLoggedInUser();
        $parameters = $event->request->getBodyParameters();
        $update = $parameters['update'] ?? [];

        $this->userValidateService->validateUpdateUserMail(['update' => $update], $user);
        if ($this->errorCollector->hasErrors()) {
            throw new \InvalidArgumentException("Couldn't update user email", 400);
        }

        /** @var string $email */
        $email = $update['email'] ?? throw new \Exception('Missing email', 500);
        $this->userActionManager->enableEmailChange(
            $user->getId() ?? throw new \Exception('User is missing its ID', 500),
            $email,
        );

        $event->setResponse(new Response('Change of user email was enabled'));
    }
}
