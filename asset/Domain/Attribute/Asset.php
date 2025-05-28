<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Asset
{
    public function __construct(
        public bool $conjoined = false,
        public bool $private = false,
    ) {
    }
}
