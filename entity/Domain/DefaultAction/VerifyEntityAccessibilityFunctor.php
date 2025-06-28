<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Domain\Attribute\Entity;

class VerifyEntityAccessibilityFunctor
{
    /**
     * @param class-string $class
     */
    public function __invoke(string $class): bool
    {
        if (!class_exists($class)) {
            return false;
        }

        return !empty((new \ReflectionClass($class))->getAttributes(Entity::class));
    }
}
