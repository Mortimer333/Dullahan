<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

interface IdentityAwareInterface
{
    public function getId(): mixed;
}
