<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

use Dullahan\Main\Entity\Asset;

interface AssetAwareInterface
{
    public function getId(): ?int;

    public function setAsset(string $column, Asset $asset): self;
}
