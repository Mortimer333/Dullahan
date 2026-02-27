<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\ResetPassword;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\ValueObject\ResetPasswordBaseline;

final class ValidateResetPasswordPayload extends EventAbstract
{
    private bool $isValid = false;
    private ?ResetPasswordBaseline $resetPassword = null;

    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        private array $payload,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<mixed> $registration
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array<mixed> $payload
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }

    public function getResetPassword(): ?ResetPasswordBaseline
    {
        return $this->resetPassword;
    }

    public function setResetPassword(?ResetPasswordBaseline $resetPassword): void
    {
        $this->resetPassword = $resetPassword;
    }
}
