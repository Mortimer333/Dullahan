<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\User\Port\Domain\UserValidationServiceInterface;
use Dullahan\User\Presentation\Event\Transport\PasswordResetValidation;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class PasswordResetValidationListener
{
    public function __construct(
        private UserValidationServiceInterface $userValidateService,
    ) {
    }

    #[AsEventListener(event: PasswordResetValidation::class)]
    public function verifyResetPassword(PasswordResetValidation $event): void
    {
        $result = $this->userValidateService->validateResetPassword(
            ['forgotten' => $event->getPayload()['forgotten'] ?? []]
        );
        $event->setIsValid($result);
    }
}
