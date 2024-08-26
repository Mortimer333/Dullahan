<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Adapter\Presentation\Event\Transport\Remove\RemoveAssetEvent;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class RemoveListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: RemoveAssetEvent::class)]
    public function onCreateAsset(RemoveAssetEvent $event): void
    {
        $this->assetFileManager->remove($event->getAsset()->structure);
        $this->assetPersistenceManager->remove($event->getAsset()->entity);
    }
}
