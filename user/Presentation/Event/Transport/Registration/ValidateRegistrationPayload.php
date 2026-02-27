<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\Registration;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\ValueObject\UserBaseline;

/**
 * @phpstan-type Registration array{
 *     register?: array<mixed>,
 * }
 */
final class ValidateRegistrationPayload extends EventAbstract
{
    private bool $isValid = false;
    private ?UserBaseline $userRegistration = null;

    /**
     * @param Registration $registration
     */
    public function __construct(
        private array $registration,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    /**
     * @return Registration $registration
     */
    public function getRegistration(): array
    {
        return $this->registration;
    }

    /**
     * @param array<mixed> $registration
     */
    public function setRegistration(array $registration): void
    {
        $this->registration = $registration;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }

    public function getUserRegistration(): ?UserBaseline
    {
        return $this->userRegistration;
    }

    public function setUserRegistration(?UserBaseline $userRegistration): void
    {
        $this->userRegistration = $userRegistration;
    }
}
