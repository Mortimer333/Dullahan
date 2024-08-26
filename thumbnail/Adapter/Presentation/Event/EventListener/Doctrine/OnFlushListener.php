<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Adapter\Presentation\Event\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Asset\Adapter\Infrastructure\Doctrine\Mapper\EntityPointersMapper;
use Dullahan\Asset\Application\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository\ThumbnailPersisterInterface;

#[AsDoctrineListener(event: Events::onFlush, priority: 256)]
class OnFlushListener
{
    public function __construct(
        protected ThumbnailPersisterInterface $thumbnailPersister,
    ) {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        foreach ($event->getObjectManager()->getUnitOfWork()->getScheduledEntityUpdates() as $entity) {
            $this->removeOrphanedThumbnails($event, $entity);
        }
    }

    protected function removeOrphanedThumbnails(OnFlushEventArgs $event, object $entity): void
    {
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $activePointers = EntityPointersMapper::getEntityActivePointers($entity);
        foreach ($activePointers as $fieldName => $pointer) {
            $getter = 'get' . ucfirst($fieldName);
            if ($entity->$getter()?->getId() === $pointer->getId()) {
                continue;
            }

            $this->thumbnailPersister->removeThumbnailsFromPointer($pointer);
        }
    }
}
