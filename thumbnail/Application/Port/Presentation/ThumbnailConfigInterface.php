<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

interface ThumbnailConfigInterface
{
    public function getCode(): string;

    public function getWidth(): ?int;

    public function getHeight(): ?int;

    public function getAutoResize(): ?bool;

    public function getCrop(): array;

    public function __toString(): string;
}
