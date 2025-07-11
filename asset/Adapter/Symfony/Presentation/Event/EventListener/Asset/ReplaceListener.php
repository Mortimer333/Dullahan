<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Replace\ReplaceAssetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class ReplaceListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: ReplaceAssetEvent::class)]
    public function onReplace(ReplaceAssetEvent $event): void
    {
        $asset = $event->getAsset();
        $updated = $this->assetFileManager->reupload($asset->structure, $event->getFile());
        $this->assetPersistenceManager->update($asset->entity, $updated);
        $event->setAsset(
            new Asset(
                $updated,
                $asset->entity,
                $asset->context,
            )
        );
    }
}
