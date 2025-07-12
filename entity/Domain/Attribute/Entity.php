<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Attribute;

use Dullahan\Entity\Port\Domain\ConstraintInheritanceAwareInterface;
use Dullahan\Entity\Port\Domain\EntityValidateConstraintInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Entity
{
    /**
     * @param class-string<EntityValidateConstraintInterface|ConstraintInheritanceAwareInterface> $constraint
     */
    public function __construct(
        public string $constraint,
    ) {
    }
}
