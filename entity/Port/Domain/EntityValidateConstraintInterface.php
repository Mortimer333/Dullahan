<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

interface EntityValidateConstraintInterface
{
    public static function create(): mixed;

    public static function update(): mixed;
}
