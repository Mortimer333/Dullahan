<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\Response\Response;
use Dullahan\User\Port\Application\UserPersistServiceInterface;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;
use Dullahan\User\Port\Domain\UserValidationServiceInterface;
use Dullahan\User\Presentation\Event\Transport\Flush;
use Dullahan\User\Presentation\Event\Transport\Manage\RemoveUser;
use Dullahan\User\Presentation\Event\Transport\Saga\RemovalSaga;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ManageSagaListener
{
    public function __construct(
        protected UserRetrieveServiceInterface $userService,
        protected UserValidationServiceInterface $userValidateService,
        protected UserPersistServiceInterface $userManageService,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[AsEventListener(event: RemovalSaga::class)]
    public function removal(RemovalSaga $event): void
    {
        $parameters = $event->request->getBodyParameters();
        $user = $this->userService->getLoggedInUser();
        $this->userValidateService->validateUserRemoval($user, $parameters['user']['password'] ?? '');
        $validationEvent = $this->eventDispatcher->dispatch(
            new RemoveUser($user, (bool) ($parameters['user']['deleteAll'] ?? false)),
        );
        if (!$validationEvent->wasRemoved()) {
            throw new \Exception('User was not removed during RemoveUser event call', 500);
        }
        $this->eventDispatcher->dispatch(new Flush($user, new Context([
            Flush::FLUSH_PURPOSE => Flush::USER_REMOVAL,
        ])));

        $event->setResponse(new Response('User removed successfully'));
    }
}
