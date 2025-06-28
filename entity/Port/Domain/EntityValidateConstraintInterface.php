<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

use Symfony\Component\Validator\Constraints as Assert;

interface EntityValidateConstraintInterface
{
    public static function create(): Assert\Collection;

    public static function update(): Assert\Collection;
}
