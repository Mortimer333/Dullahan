<?php

declare(strict_types=1);

namespace Dullahan\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Contract\AssetAwareInterface;
use Dullahan\Doctrine\Mapper\EntityPointersMapper;
use Dullahan\Service\Util\EntityUtilService;

#[AsDoctrineListener(event: Events::preRemove, priority: 500)]
class PreRemoveListener
{
    public function __construct(
        protected EntityUtilService $entityUtilService,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $event): void
    {
        $this->removeOrphanedPointersAndThumbnails($event);
    }

    /**
     * Garbage collector for orphaned pointers - remove all pointers of the removed entity.
     */
    protected function removeOrphanedPointersAndThumbnails(PreRemoveEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $activePointers = EntityPointersMapper::getEntityActivePointers($entity);
        $em = $event->getObjectManager();
        foreach ($activePointers as $pointer) {
            if ($pointer->getId()) {
                $em->remove($pointer);
            }
            $this->entityUtilService->removeFromThumbnails($pointer);
        }
    }
}
