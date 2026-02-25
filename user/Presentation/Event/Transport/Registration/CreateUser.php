<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\Registration;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\ValueObject\UserBaseline;

class CreateUser extends EventAbstract
{
    public function __construct(
        protected UserBaseline $userBaseline,
        protected ?User $user = null,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getUserBaseline(): UserBaseline
    {
        return $this->userBaseline;
    }

    public function setUserBaseline(UserBaseline $userBaseline): void
    {
        $this->userBaseline = $userBaseline;
    }
}
