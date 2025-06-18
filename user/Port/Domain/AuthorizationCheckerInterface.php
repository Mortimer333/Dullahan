<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Domain;

interface AuthorizationCheckerInterface
{
    public function canAccess(string $path, ?object $user): bool;
}
