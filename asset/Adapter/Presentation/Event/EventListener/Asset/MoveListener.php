<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Adapter\Presentation\Event\Transport\Move\MoveAssetEvent;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Domain\Asset;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class MoveListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: MoveAssetEvent::class)]
    public function onMoveAsset(MoveAssetEvent $event): void
    {
        $asset = $event->getAsset();
        $path = $event->getPath();

        $moved = $this->assetFileManager->move($asset->structure, $path);
        $this->assetPersistenceManager->update($asset->entity, $moved);
        $event->setAsset(
            new Asset(
                $moved,
                $asset->entity,
                $asset->context,
            )
        );
    }
}
