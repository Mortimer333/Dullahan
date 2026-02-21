<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Exist\AssetExistEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class ExistsListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: AssetExistEvent::class)]
    public function assetExists(AssetExistEvent $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setExists(
            $this->assetFileManager->exists($event->getPath())
            && $this->assetPersistenceManager->exists($event->getPath())
        );
    }
}
