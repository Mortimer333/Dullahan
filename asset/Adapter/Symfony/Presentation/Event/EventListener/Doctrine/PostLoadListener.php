<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Infrastructure\Mapper\EntityPointersMapper;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;

#[AsDoctrineListener(event: Events::postLoad)]
class PostLoadListener
{
    public function postLoad(PostLoadEventArgs $event): void
    {
        $this->postAssetPointerLoad($event);
        $this->postAssetAwareLoad($event);
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
            // @TODO add specific exception
            throw new \Exception('Entity class not set', 500);
        }

        $repo = $event->getObjectManager()->getRepository($entityClass);
        // @TODO maybe instead of giving entity access to repository give it lazy loaded method?
        $entity->setEntityRepository($repo);
    }

    /**
     * Part of the garbage collector for orphaned pointers
     * Save pointer relation before any changes, so we can later deduce it his "relation" got deleted and remove pointer
     * (pointers create relations with polymorphic foreign key).
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
