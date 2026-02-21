<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Remove;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Main\Model\Context;

final class PreRemoveAssetEvent
{
    public function __construct(
        private Asset $asset,
        private Context $context,
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
