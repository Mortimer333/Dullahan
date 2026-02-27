<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\ResetPassword;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;

final class CanUserResetPassword extends EventAbstract
{
    private bool $isValid = false;

    public function __construct(
        #[\SensitiveParameter] private string $token,
        private int $userId,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}
