<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\User\Domain\ValueObject\UserBaseline;
use Dullahan\User\Port\Domain\RegistrationValidationServiceInterface;
use Dullahan\User\Presentation\Event\Transport\Registration\RegistrationValidation;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class RegistrationListener
{
    public function __construct(
        private RegistrationValidationServiceInterface $registrationValidationService,
        private ErrorCollectorInterface $errorCollector,
    ) {
    }

    #[AsEventListener(event: RegistrationValidation::class)]
    public function registrationValidation(RegistrationValidation $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $registration = $event->getRegistration();
        $this->registrationValidationService->validateRegistration($registration);
        $registration = $registration['register'] ?? [];

        $this->errorCollector->setPrefixPath(['register']);
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
        $this->errorCollector->setPrefixPath([]);

        $event->setUserRegistration(new UserBaseline(
            $registration['username'],
            $registration['email'],
            $registration['password'],
            $registration['passwordRepeat'],
        ));
        $event->setIsValid(!$this->errorCollector->hasErrors());
    }
}
