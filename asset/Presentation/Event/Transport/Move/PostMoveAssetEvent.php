<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Move;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;

final class PostMoveAssetEvent
{
    public function __construct(
        protected Asset $asset,
        protected string $path,
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

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
