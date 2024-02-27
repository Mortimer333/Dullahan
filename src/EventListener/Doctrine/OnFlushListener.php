<?php

declare(strict_types=1);

namespace Dullahan\EventListener\Doctrine;

use App\Entity\Cod\Monster;
use App\Entity\Cod\MonsterRandomization;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Doctrine\Mapper\EntityInheritanceMapper;
use Dullahan\Doctrine\Mapper\EntityPointersMapper;
use Dullahan\Service\Util\EntityUtilService;
use Dullahan\Contract\AssetAwareInterface;
use Dullahan\Contract\InheritanceAwareInterface;

#[AsDoctrineListener(event: Events::onFlush, priority: 500)]
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

        $entities = [
            ...$uow->getScheduledEntityUpdates(),
            ...$uow->getScheduledEntityInsertions(),
        ];
        foreach ($entities as $entity) {
            $this->updateChildrenRelationPath($event, $entity);
            $this->removeOrphanedPointersAndThumbnails($event, $entity);
            // TODO #randomization
            //            $this->manageMonsterRandomization($event, $entity);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->removeConjoinedAssets($entity);
        }
    }

    // TODO #randomization add randomizable interface
    //    protected function manageMonsterRandomization(OnFlushEventArgs $event, object $entity): void
    //    {
    //        if (!$entity instanceof Monster) {
    //            return;
    //        }
    //
    //        if ($entity->getType() || $entity->getSubtype()) {
    //            $this->createMonsterRandomization($event, $entity);
    //        }
    //    }
    //
    //    protected function createMonsterRandomization(OnFlushEventArgs $event, Monster $entity): void
    //    {
    //        $em = $event->getObjectManager();
    //        $rootPathId = $entity->getRootId();
    //        $type = $entity->getInherited('type');
    //        $subtype = $entity->getInherited('subtype');
    //        $randomization = $entity->getRandomization();
    //        if (!is_null($rootPathId)) {
    //            $duplicate = $em->getRepository(MonsterRandomization::class)->findOneBy([
    //                'rootParentId' => $rootPathId,
    //                'type' => $type,
    //                'subtype' => $subtype,
    //            ]);
    //            if ($duplicate) {
    //                return;
    //            }
    //        }
    //        if (!$randomization) {
    //            $randomization = new MonsterRandomization();
    //            $entity->setRandomization($randomization);
    //            $em->persist($randomization);
    //            $this->computeEntity($em, $randomization);
    //        }
    //
    //        $randomization->setType($type);
    //        $randomization->setSubtype($subtype);
    //        $randomization->setExpansion($entity->getInherited('expansion'));
    //        $randomization->setMonsterPack($entity->getInherited('pack'));
    //        $randomization->setRootParentId($rootPathId);
    //
    //        $this->recomputeEntity($em, $entity);
    //        $this->recomputeEntity($em, $randomization);
    //    }

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
    protected function removeOrphanedPointersAndThumbnails(OnFlushEventArgs $event, object $entity): void
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
            $this->entityUtilService->removeFromThumbnails($pointer);
        }
    }

    protected function updateChildrenRelationPath(OnFlushEventArgs $event, object $entity): void
    {
        if (!$entity instanceof InheritanceAwareInterface) {
            return;
        }

        $em = $event->getObjectManager();
        if (!$entity->getParent()) {
            $entity->setRelationPath(null);
        } else {
            $parentPath = $entity->getParent()->getRelationPath();
            if (is_null($parentPath)) {
                $parentPath = (string) $entity->getParent()->getId();
            } else {
                $parentPath .= ',' . $entity->getParent()->getId();
            }

            // Is being created
            if (!$entity->getId()) {
                $entity->setRelationPath($parentPath);
                $this->recomputeEntity($em, $entity);

                return;
            }
        }

        $this->recomputeEntity($em, $entity);
        if (!EntityInheritanceMapper::didParentChange($entity)) {
            return;
        }

        $parentPath = trim($entity->getRelationPath() . ',' . $entity->getId(), ',');
        $this->assignNewRelationPathToChildren($em, $entity->getChildren(), $parentPath);
    }

    protected function recomputeEntity(EntityManagerInterface $em, object $entity): void
    {
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata($entity::class), $entity);
    }

    protected function computeEntity(EntityManagerInterface $em, object $entity): void
    {
        $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata($entity::class), $entity);
    }

    protected function assignNewRelationPathToChildren(
        EntityManagerInterface $em,
        Collection $children,
        string $path,
    ): void {
        /** @var InheritanceAwareInterface $child */
        foreach ($children as $child) {
            $child->setRelationPath($path);
            $em->persist($child);
            $this->recomputeEntity($em, $child);
            $this->assignNewRelationPathToChildren(
                $em,
                $child->getChildren(),
                $path . ',' . $child->getId(),
            );
        }
    }
}
