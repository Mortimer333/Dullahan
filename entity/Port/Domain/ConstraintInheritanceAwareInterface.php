<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

interface ConstraintInheritanceAwareInterface
{
    public static function createChild(): mixed;

    public static function updateChild(): mixed;
}
