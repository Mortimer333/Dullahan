<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Domain\Exception\AssetEntityNotFoundException;
use Dullahan\Asset\Domain\Exception\AssetNotFoundException;
use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Retrieve\RetrieveByIdEvent;
use Dullahan\Asset\Presentation\Event\Transport\Retrieve\RetrieveByPathEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class RetrieveListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
    ) {
    }

    #[AsEventListener(event: RetrieveByPathEvent::class)]
    public function onRetrieveByPath(RetrieveByPathEvent $event): void
    {
        try {
            $path = $event->getPath();
            $event->setEntity($this->assetPersistenceManager->getByPath($path));
            $event->setStructure($this->assetFileManager->get($path));
        } catch (AssetEntityNotFoundException|AssetNotFoundException) {
            $event->setEntity(null);
            $event->setStructure(null);
            // Do nothing
        }
    }

    #[AsEventListener(event: RetrieveByIdEvent::class)]
    public function onRetrieveById(RetrieveByIdEvent $event): void
    {
        try {
            $entity = $this->assetPersistenceManager->get((int) $event->getId());
            $event->setEntity($entity);
            $event->setStructure($this->assetFileManager->get($entity->getFullPath()));
        } catch (AssetEntityNotFoundException|AssetNotFoundException) {
            $event->setEntity(null);
            $event->setStructure(null);
            // Do nothing
        }
    }
}
