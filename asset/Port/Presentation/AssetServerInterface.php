<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Asset\Domain\Structure;

interface AssetServerInterface
{
    public function serve(Structure $asset): void;
}
