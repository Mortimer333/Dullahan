<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\User\Port\Domain\RegistrationValidationServiceInterface;
use Dullahan\User\Presentation\Event\Transport\RegistrationValidation;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class RegistrationValidationListener
{
    public function __construct(
        protected RegistrationValidationServiceInterface $registrationValidationService,
        protected ErrorCollectorInterface $errorCollector,
    ) {
    }

    #[AsEventListener(event: RegistrationValidation::class)]
    public function onGetCSRF(RegistrationValidation $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $registration = $event->getRegistration();
        $this->registrationValidationService->validateRegistration($registration);
        if (isset($registration['password']) && isset($registration['passwordRepeat'])) {
            $this->registrationValidationService->validateUserPassword(
                (string) $registration['password'],
                (string) $registration['passwordRepeat'],
            );
        }
        if (isset($registration['email']) && isset($registration['username'])) {
            $this->registrationValidationService->validateUserUniqueness(
                (string) $registration['email'],
                (string) $registration['username'],
            );
        }
        $event->setIsValid(!$this->errorCollector->hasErrors());
    }
}
