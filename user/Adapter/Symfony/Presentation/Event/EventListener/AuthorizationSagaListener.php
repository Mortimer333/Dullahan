<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Model\Response\Response;
use Dullahan\User\Port\Application\Manager\UserActionManagerInterface;
use Dullahan\User\Port\Application\Manager\UserPersistManagerInterface;
use Dullahan\User\Presentation\Event\Transport\ForgottenPassword\ValidateForgottenPasswordPayload;
use Dullahan\User\Presentation\Event\Transport\Registration\ValidateRegistrationPayload;
use Dullahan\User\Presentation\Event\Transport\Saga\ForgottenPasswordSaga;
use Dullahan\User\Presentation\Event\Transport\Saga\RegistrationSaga;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class AuthorizationSagaListener
{
    public function __construct(
        private UserPersistManagerInterface $userPersistManager,
        private EventDispatcherInterface $eventDispatcher,
        private UserActionManagerInterface $userActionManager,
    ) {
    }

    #[AsEventListener(event: RegistrationSaga::class)]
    public function registration(RegistrationSaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        // Validate request - something unique to the Saga
        $key = 'register';
        $registration = $event->request->getBodyParameter($key, []);
        $validationEvent = $this->eventDispatcher->dispatch(
            new ValidateRegistrationPayload([$key => $registration]),
        );
        if (!$validationEvent->isValid()) {
            throw new \InvalidArgumentException('Registration attempt failed', 400);
        }

        if (!$validationEvent->getUserRegistration()) {
            throw new \Exception('Registration validation attempt not handled properly, missing User Registration object', 500);
        }

        $baseline = $validationEvent->getUserRegistration();
        $this->userPersistManager->create(
            $baseline->username,
            $baseline->email,
            $baseline->password,
        );

        $event->setResponse(new Response('User registered'));
    }

    #[AsEventListener(event: ForgottenPasswordSaga::class)]
    public function forgottenPassword(ForgottenPasswordSaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        // Validate request - something unique to the Saga
        $key = 'forgotten';
        $payload = $event->request->getBodyParameter($key, []);
        $validationEvent = $this->eventDispatcher->dispatch(
            new ValidateForgottenPasswordPayload([$key => $payload]),
        );
        if (!$validationEvent->isValid()) {
            throw new \InvalidArgumentException('Reset forgotten password attempt failed', 400);
        }
        if (!$validationEvent->getForgottenPassword()) {
            throw new \Exception('Forgotten password validation attempt not handled properly, missing Forgotten Password object', 500);
        }

        $this->userActionManager->enablePasswordReset($validationEvent->getForgottenPassword()->email);

        $event->setResponse(new Response('Password reset has finished successfully'));
    }
}
