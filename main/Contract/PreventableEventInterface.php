<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

interface PreventableEventInterface
{
    public function preventDefault(): void;

    public function wasDefaultPrevented(): bool;
}
