<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Adapter\Symfony\Presentation\Event\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Asset\Infrastructure\Mapper\EntityPointersMapper;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Thumbnail\Port\Infrastructure\Database\Repository\ThumbnailPersisterInterface;

#[AsDoctrineListener(event: Events::preRemove, priority: 20)]
class PreRemoveListener
{
    public function __construct(
        protected ThumbnailPersisterInterface $thumbnailPersister,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $event): void
    {
        $this->removeOrphanedThumbnails($event);
    }

    protected function removeOrphanedThumbnails(PreRemoveEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $activePointers = EntityPointersMapper::getEntityActivePointers($entity);
        foreach ($activePointers as $pointer) {
            $this->thumbnailPersister->removeThumbnailsFromPointer($pointer);
        }
    }
}
