<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\User\Port\Application\UserPersistServiceInterface;
use Dullahan\User\Presentation\Event\Transport\ForgottenPassword\EnablePasswordReset;
use Dullahan\User\Presentation\Event\Transport\ResetPassword\ResetPassword;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class PasswordListener
{
    public function __construct(
        private UserPersistServiceInterface $userPersistService,
    ) {
    }

    #[AsEventListener(event: EnablePasswordReset::class)]
    public function enablePasswordReset(EnablePasswordReset $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $this->userPersistService->enablePasswordReset($event->getUser());
    }

    #[AsEventListener(event: ResetPassword::class)]
    public function resetPassword(ResetPassword $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $this->userPersistService->resetPassword($event->getUser(), $event->getPassword());
    }
}
