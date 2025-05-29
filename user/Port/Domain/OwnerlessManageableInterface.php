<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Domain;

interface OwnerlessManageableInterface
{
    public function getId(): ?int;
}
