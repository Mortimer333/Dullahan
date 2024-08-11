<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

use Dullahan\Main\Entity\User;
use Dullahan\Main\Entity\UserData;

interface ManageableInterface
{
    public function getId(): ?int;

    public function isOwner(User $user): bool;

    public function setOwner(User $user): self;

    public function getUserData(): ?UserData;

    public function setUserData(?UserData $userData): self;
}
