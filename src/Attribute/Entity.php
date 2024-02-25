<?php

declare(strict_types=1);

namespace Dullahan\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Entity
{
    public function __construct(
        public string $constraint,
    ) {
    }
}
