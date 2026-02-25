<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Model\Response\Response;
use Dullahan\User\Port\Application\Manager\UserPersistManagerInterface;
use Dullahan\User\Presentation\Event\Transport\Saga\RegistrationSaga;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class AuthorizationSagaListener
{
    public function __construct(
        private UserPersistManagerInterface $userPersistManager,
    ) {
    }

    #[AsEventListener(event: RegistrationSaga::class)]
    public function registration(RegistrationSaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $this->userPersistManager->create($event->request->getBodyParameters());

        $event->setResponse(new Response('User registered'));
    }
}
