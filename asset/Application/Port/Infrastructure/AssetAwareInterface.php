<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Port\Infrastructure;

use Dullahan\Asset\Entity\Asset;

interface AssetAwareInterface
{
    public function getId(): ?int;

    public function setAsset(string $column, Asset $asset): self;
}
