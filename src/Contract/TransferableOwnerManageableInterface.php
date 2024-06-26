<?php

declare(strict_types=1);

namespace Dullahan\Contract;

use Dullahan\Entity\User;

interface TransferableOwnerManageableInterface
{
    public function getId(): ?int;

    public function isOwner(User $user): bool;
}
