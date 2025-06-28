<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

use Dullahan\Main\Contract\PreventableEventInterface;

abstract class EventAbstract implements PreventableEventInterface
{
    private bool $defaultPrevented = false;

    public function __construct(
        public Context $context = new Context(),
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
