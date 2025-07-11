<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Domain;

use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;

interface ManageableInterface
{
    public function getId(): ?int;

    public function isOwner(User $user): bool;

    public function setOwner(User $user): self;

    public function getUserData(): ?UserData;

    public function setUserData(?UserData $userData): self;
}
