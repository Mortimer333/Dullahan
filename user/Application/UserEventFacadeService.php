<?php

declare(strict_types=1);

namespace Dullahan\User\Application;

use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Model\Context;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Port\Application\Manager\UserPersistManagerInterface;
use Dullahan\User\Presentation\Event\Transport\Flush;
use Dullahan\User\Presentation\Event\Transport\Registration\CreateUser;
use Dullahan\User\Presentation\Event\Transport\Registration\RegistrationValidation;

class UserEventFacadeService
implements UserPersistManagerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function create(array $payload): User
    {
        // @TODO:
        // - check user permissions for other user creation
        // - custom generic exceptions
        // - I hate how we basically require to wrap payload with `register` - very useful for API, awful for normal use
        // so that our error bag works without issue.
        // Maybe we have to our change approach of handling settings multi-errors?
        $registrationKey = 'register';
        $registration = $payload[$registrationKey] ?? [];
        $validationEvent = $this->eventDispatcher->dispatch(
            new RegistrationValidation([$registrationKey => $registration]),
        );
        if (!$validationEvent->isValid()) {
            throw new \InvalidArgumentException('Registration attempt failed', 400);
        }

        if (!$validationEvent->getUserRegistration()) {
            throw new \Exception('Registration validation attempt not handled properly, missing User Registration object', 500);
        }

        $registerUserEvent = $this->eventDispatcher->dispatch(
            new CreateUser($validationEvent->getUserRegistration()),
        );
        if (!$registerUserEvent->getUser()) {
            throw new \Exception('Registration attempt not handled', 500);
        }

        $flush = $this->eventDispatcher->dispatch(new Flush($registerUserEvent->getUser(), new Context([
            Flush::FLUSH_PURPOSE => Flush::REGISTER,
        ])));

        return $flush->user;
    }

    //    public function update(int $id, array $payload): User
    //    {
    //        // TODO: Implement update() method.
    //    }
    //
    //    public function remove(int $id): bool
    //    {
    //        // TODO: Implement remove() method.
    //    }
}
