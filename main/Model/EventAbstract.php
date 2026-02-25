<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

use Dullahan\Main\Contract\PreventableEventInterface;

abstract class EventAbstract implements PreventableEventInterface
{
    public const PRIORITY_FIRST = 256;
    public const PRIORITY_LAST = -256;

    private bool $defaultPrevented = false;

    public function __construct(
        public readonly Context $context = new Context(),
    ) {
    }

    public function preventDefault(): void
    {
        $this->defaultPrevented = true;
    }

    public function wasDefaultPrevented(): bool
    {
        return $this->defaultPrevented;
    }
}
