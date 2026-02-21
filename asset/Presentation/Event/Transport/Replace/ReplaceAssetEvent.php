<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Replace;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Port\Presentation\NewStructureInterface;
use Dullahan\Main\Model\Context;

final class ReplaceAssetEvent
{
    public function __construct(
        private Asset $asset,
        private NewStructureInterface $file,
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
