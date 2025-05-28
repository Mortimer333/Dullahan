<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;

interface AssetPointerInterface
{
    public function getId(): mixed;

    public function getAsset(): ?AssetEntityInterface;

    public function setAsset(?AssetEntityInterface $asset): self;

    /**
     * Returns Entity with connection to pointer's asset defined by this pointer.
     */
    public function getEntity(): ?AssetAwareInterface;
}
