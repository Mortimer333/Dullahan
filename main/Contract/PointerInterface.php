<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

interface PointerInterface
{
    /**
     * Returns pointed entity.
     */
    public function getOrigin(): ?object;

    /**
     * Returns entity which holds pointer.
     */
    public function getEntity(): ?object;
}
