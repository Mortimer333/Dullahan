<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\Transport\Replace;

use Dullahan\Asset\Application\Port\Presentation\NewStructureInterface;
use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;

final class ReplaceAssetEvent
{
    public function __construct(
        protected Asset $asset,
        protected NewStructureInterface $file,
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

    public function getFile(): NewStructureInterface
    {
        return $this->file;
    }

    public function setFile(NewStructureInterface $file): void
    {
        $this->file = $file;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
