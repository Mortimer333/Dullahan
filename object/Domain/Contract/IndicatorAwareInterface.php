<?php

declare(strict_types=1);

namespace Dullahan\Object\Domain\Contract;

interface IndicatorAwareInterface
{
    public function getParentField(): string;

    public function getParent(): ?object;

    public function getIndicator(): ?int;
}
