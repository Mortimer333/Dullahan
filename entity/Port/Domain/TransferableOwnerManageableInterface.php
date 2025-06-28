<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

use Dullahan\User\Domain\Entity\User;

interface TransferableOwnerManageableInterface
{
    public function getId(): ?int;

    public function isOwner(User $user): bool;
}
