<?php

declare(strict_types=1);

namespace Dullahan\User\Application;

use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Model\Context;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Exception\UserNotFoundException;
use Dullahan\User\Domain\ValueObject\UserBaseline;
use Dullahan\User\Port\Application\Manager\UserActionManagerInterface;
use Dullahan\User\Port\Application\Manager\UserPersistManagerInterface;
use Dullahan\User\Port\Application\Manager\UserStatusManagerInterface;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;
use Dullahan\User\Presentation\Event\Transport\Flush;
use Dullahan\User\Presentation\Event\Transport\ForgottenPassword\EnablePasswordReset;
use Dullahan\User\Presentation\Event\Transport\Registration\CreateUser;
use Dullahan\User\Presentation\Event\Transport\ResetPassword\CanUserResetPassword;
use Dullahan\User\Presentation\Event\Transport\ResetPassword\ResetPassword;

class UserEventFacadeService
implements UserPersistManagerInterface, UserActionManagerInterface, UserStatusManagerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private UserRetrieveServiceInterface $userRetrieveService,
    ) {
    }

    public function create(
        string $username,
        string $email,
        string $password,
    ): User {
        // @TODO:
        // - check user permissions for other user creation
        // - custom generic exceptions
        // - I hate how we basically require to wrap payload with `register` - very useful for API, awful for normal use
        // so that our error bag works without issue.
        // Maybe we have to our change approach of handling settings multi-errors?
        $registerUserEvent = $this->eventDispatcher->dispatch(
            new CreateUser(
                new UserBaseline(
                    $username,
                    $email,
                    $password,
                    $password,
                ),
            ),
        );
        if (!$registerUserEvent->getUser()) {
            throw new \Exception('Registration attempt not handled', 500);
        }

        $flush = $this->eventDispatcher->dispatch(new Flush($registerUserEvent->getUser(), new Context([
            Flush::FLUSH_PURPOSE => Flush::REGISTER,
        ])));

        return $flush->user;
    }

    public function enablePasswordReset(string $email): void
    {
        try {
            // We are manually retrieving user to avoid permissions issues
            $user = $this->userRetrieveService->getByEmail($email);
        } catch (UserNotFoundException $e) {
            return; // Do not notify user if nothing was found
        }

        $enableEvent = $this->eventDispatcher->dispatch(new EnablePasswordReset($user));
        $this->eventDispatcher->dispatch(new Flush($enableEvent->getUser(), new Context([
            Flush::FLUSH_PURPOSE => Flush::ENABLE_PASSWORD_RESET,
        ])));
    }

    public function canResetPassword(int $id, #[\SensitiveParameter] string $token): bool
    {
        return $this->eventDispatcher->dispatch(new CanUserResetPassword($token, $id))->isValid();
    }

    public function resetPassword(int $id, #[\SensitiveParameter] string $password): void
    {
        $user = $this->userRetrieveService->get($id);
        $this->eventDispatcher->dispatch(new ResetPassword($user, $password));
        $this->eventDispatcher->dispatch(new Flush($user, new Context([
            Flush::FLUSH_PURPOSE => Flush::PASSWORD_RESET,
        ])));
    }
}
