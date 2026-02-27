<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\User\Port\Application\UserPersistServiceInterface;
use Dullahan\User\Presentation\Event\Transport\ForgottenPassword\EnablePasswordReset;
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
}
