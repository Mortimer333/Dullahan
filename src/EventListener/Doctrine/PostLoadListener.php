<?php

declare(strict_types=1);

namespace Dullahan\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Constraint\EntityManagerInjectionInterface;
use Dullahan\Contract\AssetAwareInterface;
use Dullahan\Contract\InheritanceAwareInterface;
use Dullahan\Doctrine\Mapper\EntityInheritanceMapper;
use Dullahan\Doctrine\Mapper\EntityPointersMapper;
use Dullahan\Entity\AssetPointer;
use Dullahan\Service\Util\EntityUtilService;

#[AsDoctrineListener(event: Events::postLoad)]
class PostLoadListener
{
    public function __construct(
        protected EntityUtilService $entityUtilService,
    ) {
    }

    public function postLoad(PostLoadEventArgs $event): void
    {
        $this->postAssetPointerLoad($event);
        $this->postAssetAwareLoad($event);
        $this->postInheritanceAwareLoad($event);
        $this->postEntityInject($event);
    }

    protected function postEntityInject(PostLoadEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof EntityManagerInjectionInterface) {
            return;
        }

        $entity->setEntityManager($event->getObjectManager());
    }

    protected function postInheritanceAwareLoad(PostLoadEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof InheritanceAwareInterface) {
            return;
        }

        EntityInheritanceMapper::addInheritedParent($entity);
    }

    protected function postAssetPointerLoad(PostLoadEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof AssetPointer) {
            return;
        }

        /** @var class-string $entityClass */
        $entityClass = $entity->getEntityClass();
        if (!$entityClass) {
            throw new \Exception('Entity class not set', 500);
        }

        $repo = $event->getObjectManager()->getRepository($entityClass);
        $entity->setEntityRepository($repo);
    }

    /**
     * Garbage collector for orphaned pointers - here saves relation before changes on the object.
     */
    protected function postAssetAwareLoad(PostLoadEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $meta = (array) $event->getObjectManager()->getClassMetadata($entity::class);
        foreach ($meta['associationMappings'] as $mapping) {
            if (AssetPointer::class !== $mapping['targetEntity']) {
                continue;
            }

            $fieldName = $mapping['fieldName'];
            EntityPointersMapper::setActivePointer($entity, $fieldName);
        }
    }
}
