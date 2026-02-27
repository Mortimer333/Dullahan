<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\User\Domain\ValueObject\ForgotPasswordBaseline;
use Dullahan\User\Domain\ValueObject\UserBaseline;
use Dullahan\User\Port\Domain\RegistrationValidationServiceInterface;
use Dullahan\User\Port\Domain\UserValidationServiceInterface;
use Dullahan\User\Presentation\Event\Transport\ForgottenPassword\ValidateForgottenPasswordPayload;
use Dullahan\User\Presentation\Event\Transport\Registration\ValidateRegistrationPayload;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class ValidationListener
{
    public function __construct(
        private RegistrationValidationServiceInterface $registrationValidationService,
        private ErrorCollectorInterface $errorCollector,
        private UserValidationServiceInterface $userValidateService,
    ) {
    }

    #[AsEventListener(event: ValidateRegistrationPayload::class)]
    public function registrationValidation(ValidateRegistrationPayload $event): void
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

    #[AsEventListener(event: ValidateForgottenPasswordPayload::class)]
    public function forgottenPasswordValidation(ValidateForgottenPasswordPayload $event): void
    {
        $forgotten = $event->getPayload();
        $event->setIsValid($this->userValidateService->validateForgottenPassword($forgotten));
        $event->setForgottenPassword(new ForgotPasswordBaseline(
            $forgotten['forgotten']['mail'] ?? throw new \Exception('Email not found', 500),
        ));
    }
}
