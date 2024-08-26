<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Asset\Adapter\Infrastructure\Doctrine\Mapper\EntityPointersMapper;
use Dullahan\Asset\Application\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Main\Service\Util\EntityUtilService;

#[AsDoctrineListener(event: Events::onFlush, priority: 256)]
class OnFlushListener
{
    public function __construct(
        protected EntityUtilService $entityUtilService,
    ) {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->removeOrphanedPointers($event, $entity);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->removeConjoinedAssets($entity);
        }
    }

    protected function removeConjoinedAssets(object $entity): void
    {
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $activePointers = EntityPointersMapper::getEntityActivePointers($entity);
        foreach ($activePointers as $fieldName => $pointer) {
            $this->entityUtilService->removeConjoinedAsset($entity, $fieldName, $pointer);
        }
    }

    /**
     * Garbage collector for orphaned pointers - remove all replaced pointers.
     */
    protected function removeOrphanedPointers(OnFlushEventArgs $event, object $entity): void
    {
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $activePointers = EntityPointersMapper::getEntityActivePointers($entity);
        $em = $event->getObjectManager();
        foreach ($activePointers as $fieldName => $pointer) {
            $getter = 'get' . ucfirst($fieldName);
            if ($entity->$getter()?->getId() === $pointer->getId()) {
                continue;
            }

            if ($pointer->getId()) {
                $em->remove($pointer);
                $this->entityUtilService->removeConjoinedAsset($entity, $fieldName, $pointer);
            }
        }
    }
}
