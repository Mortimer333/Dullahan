<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\Manage;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

final class FinishChangingEmail extends EventAbstract
{
    public function __construct(
        private User $user,
        #[\SensitiveParameter] private string $token,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
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
