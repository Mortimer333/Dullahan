<?php

declare(strict_types=1);

namespace Dullahan\Main\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Main\Contract\InheritanceAwareInterface;
use Dullahan\Main\Doctrine\Mapper\EntityInheritanceMapper;
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

        $entities = [
            ...$uow->getScheduledEntityUpdates(),
            ...$uow->getScheduledEntityInsertions(),
        ];
        foreach ($entities as $entity) {
            $this->updateChildrenRelationPath($event, $entity);
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

    /**
     * @param Collection<int, InheritanceAwareInterface> $children
     */
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
