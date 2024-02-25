<?php

declare(strict_types=1);

namespace Dullahan\Contract;

use Symfony\Component\Validator\Constraints as Assert;

interface ConstraintInheritanceAwareInterface
{
    public static function createChild(): Assert\Collection;

    public static function updateChild(): Assert\Collection;
}
