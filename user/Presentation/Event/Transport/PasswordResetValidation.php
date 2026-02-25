<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;

final class PasswordResetValidation extends EventAbstract
{
    private bool $isValid = false;

    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        private RequestInterface $request,
        private array $payload,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
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
}
