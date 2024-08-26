<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Adapter\Presentation\Event\Transport\Exist\AssetExistEvent;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class ExistsListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: AssetExistEvent::class)]
    public function postCreateAsset(AssetExistEvent $event): void
    {
        $event->setExists(
            $this->assetFileManager->exists($event->getPath())
            && $this->assetPersistenceManager->exists($event->getPath())
        );
    }
}
