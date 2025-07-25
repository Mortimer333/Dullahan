<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Remove\RemoveAssetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class RemoveListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: RemoveAssetEvent::class)]
    public function onRemoveAsset(RemoveAssetEvent $event): void
    {
        $this->assetFileManager->remove($event->getAsset()->structure);
        $this->assetPersistenceManager->remove($event->getAsset()->entity);
    }
}
