<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

abstract class EventAbstract
{
    private bool $defaultPrevented = false;

    public function preventDefault(): void
    {
        $this->defaultPrevented = true;
    }

    public function wasDefaultPrevented(): bool
    {
        return $this->defaultPrevented;
    }
}
