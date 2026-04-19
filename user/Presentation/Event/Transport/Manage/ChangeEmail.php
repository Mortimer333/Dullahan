<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\Manage;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

final class ChangeEmail extends EventAbstract
{
    public function __construct(
        private User $user,
        private string $email,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
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
