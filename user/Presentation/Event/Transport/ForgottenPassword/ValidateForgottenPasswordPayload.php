<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\ForgottenPassword;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\ValueObject\ForgotPasswordBaseline;

/**
 * @phpstan-type ForgottenPassword array{
 *     forgotten?: array<mixed>,
 * }
 */
final class ValidateForgottenPasswordPayload extends EventAbstract
{
    private bool $isValid = false;
    private ?ForgotPasswordBaseline $forgottenPassword = null;

    /**
     * @param ForgottenPassword $payload
     */
    public function __construct(
        private array $payload,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    /**
     * @return ForgottenPassword
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

    public function getForgottenPassword(): ?ForgotPasswordBaseline
    {
        return $this->forgottenPassword;
    }

    public function setForgottenPassword(?ForgotPasswordBaseline $forgottenPassword): void
    {
        $this->forgottenPassword = $forgottenPassword;
    }
}
