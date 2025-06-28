<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\EventAbstract;

final class RegistrationValidation extends EventAbstract
{
    private bool $isValid = false;

    /**
     * @param array<mixed> $registration
     */
    public function __construct(
        private RequestInterface $request,
        private array $registration,
    ) {
        parent::__construct();
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return array<mixed> $registration
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
}
