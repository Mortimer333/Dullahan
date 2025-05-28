<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Asset\Infrastructure\Mapper\EntityPointersMapper;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Main\Service\Util\EntityUtilService;

#[AsDoctrineListener(event: Events::preRemove, priority: 10)]
class PreRemoveListener
{
    public function __construct(
        protected EntityUtilService $entityUtilService,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $event): void
    {
        $this->removeOrphanedPointers($event);
    }

    /**
     * Garbage collector for orphaned pointers - remove all pointers assigned to the removed entity.
     */
    protected function removeOrphanedPointers(PreRemoveEventArgs $event): void
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
        }
    }
}
