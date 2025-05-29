<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

use Dullahan\User\Domain\Entity\User;

interface TransferableOwnerManageableInterface
{
    public function getId(): ?int;

    public function isOwner(User $user): bool;
}
