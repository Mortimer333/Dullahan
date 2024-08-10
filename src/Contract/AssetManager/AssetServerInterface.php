<?php

declare(strict_types=1);

namespace Dullahan\Contract\AssetManager;

interface AssetServerInterface
{
    public function serve(AssetInterface $asset): void;
}
