<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Flush\FlushAssetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class FlushListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: FlushAssetEvent::class, priority: -256)]
    public function flushAsset(): void
    {
        $this->assetPersistenceManager->flush();
        $this->assetFileManager->flush();
    }
}
