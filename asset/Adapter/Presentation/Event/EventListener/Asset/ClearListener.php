<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Adapter\Presentation\Event\Transport\Clear\ClearAssetEvent;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
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
