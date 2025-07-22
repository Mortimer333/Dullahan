<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

interface IndicatorAwareInterface
{
    public function getParentField(): string;

    public function getParent(): ?object;

    public function setIndicator(int $indicator): self;

    public function getIndicator(): ?int;
}
