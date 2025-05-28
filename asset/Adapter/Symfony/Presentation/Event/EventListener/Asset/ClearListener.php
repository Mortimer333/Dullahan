<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Clear\ClearAssetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class ClearListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: ClearAssetEvent::class, priority: -256)]
    public function clearAsset(): void
    {
        $this->assetPersistenceManager->clear();
        $this->assetFileManager->clear();
    }
}
