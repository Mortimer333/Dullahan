<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\Manage;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

class RemoveUser extends EventAbstract
{
    protected bool $wasRemoved = false;

    public function __construct(
        public readonly User $user,
        protected bool $deleteAll,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    public function shouldDeleteAll(): bool
    {
        return $this->deleteAll;
    }

    public function setDeleteAll(bool $deleteAll): void
    {
        $this->deleteAll = $deleteAll;
    }

    public function wasRemoved(): bool
    {
        return $this->wasRemoved;
    }

    public function setWasRemoved(bool $wasRemoved): void
    {
        $this->wasRemoved = $wasRemoved;
    }
}
