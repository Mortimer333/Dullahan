<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Infrastructure;

use Dullahan\Asset\Domain\Entity\Asset;

interface AssetAwareInterface
{
    public function getId(): ?int;

    public function setAsset(string $column, Asset $asset): self;
}
