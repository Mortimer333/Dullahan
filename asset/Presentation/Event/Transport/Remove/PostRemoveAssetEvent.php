<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Remove;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;

final class PostRemoveAssetEvent
{
    public function __construct(
        protected Asset $asset,
        protected Context $context,
    ) {
    }

    public function getAsset(): Asset
    {
        return $this->asset;
    }

    public function setAsset(Asset $asset): void
    {
        $this->asset = $asset;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
