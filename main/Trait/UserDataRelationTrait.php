<?php

declare(strict_types=1);

namespace Dullahan\Main\Trait;

use Dullahan\Main\Entity\User;

trait UserDataRelationTrait
{
    public function getUser(): ?User
    {
        return $this->userData?->getUser();
    }

    public function setUser(?User $user): self
    {
        $this->userData = $user?->getData();

        return $this;
    }

    public function isOwner(User $user): bool
    {
        if (!$this->getUser()?->getId() || !$user->getId()) {
            return false;
        }

        return $this->getUser()->getId() === $user->getId();
    }

    public function setOwner(User $user): self
    {
        $this->setUser($user);

        return $this;
    }
}
