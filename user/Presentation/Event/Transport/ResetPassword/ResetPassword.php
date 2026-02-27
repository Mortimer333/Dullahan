<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\ResetPassword;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

final class ResetPassword extends EventAbstract
{
    private bool $isValid = false;

    public function __construct(
        private User $user,
        #[\SensitiveParameter] private string $password,
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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
