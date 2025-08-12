<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Asset\Adapter\Symfony\Infrastructure\Doctrine\Repository\AssetPointerRepository;
use Dullahan\Asset\Domain\Attribute\Asset;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Infrastructure\Mapper\EntityPointersMapper;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Asset\Port\Presentation\AssetServiceInterface;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\PersistUpdatedEntity;
use Dullahan\Entity\Presentation\Event\Transport\RemoveEntity;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelInterface;

class RemoveConjoinedListener
{
    public function __construct(
        protected AssetServiceInterface $assetService,
        protected KernelInterface $kernel,
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
        protected AssetPointerRepository $assetPointerRepository,
    ) {
    }

    #[AsEventListener(event: PersistUpdatedEntity::class, priority: 10)]
    public function onPersistUpdatedEntity(PersistUpdatedEntity $event): void
    {
        $this->removeOrphanedPointers($event->entity);
    }

    #[AsEventListener(event: RemoveEntity::class, priority: 10)]
    public function onRemoveEntity(RemoveEntity $event): void
    {
        $this->removeConjoinedAssets($event->entity);
    }

    /**
     * Garbage collector for orphaned pointers - remove all replaced pointers.
     */
    protected function removeOrphanedPointers(object $entity): void
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

            if ($pointer->getId()) {
                $this->assetPointerRepository->remove($pointer);
                $this->removeConjoinedAsset($entity, $fieldName, $pointer);
            }
        }
    }

    protected function removeConjoinedAssets(object $entity): void
    {
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $activePointers = EntityPointersMapper::getEntityActivePointers($entity);
        foreach ($activePointers as $fieldName => $pointer) {
            $this->removeConjoinedAsset($entity, $fieldName, $pointer);
        }
    }

    protected function removeConjoinedAsset(AssetAwareInterface $entity, string $field, AssetPointer $pointer): void
    {
        $class = $this->entityDefinitionManager->getEntityTrueClass($entity);
        if (!$class) {
            throw new \Exception(sprintf('Entity %s true class was not found', $class), 500);
        }

        $reflectionClass = new \ReflectionClass($class);

        if (!$reflectionClass->hasProperty($field)) {
            throw new \Exception(sprintf('Entity %s is missing %s property', $class, $field), 500);
        }

        $property = $reflectionClass->getProperty($field);
        $assets = $property->getAttributes(Asset::class);
        if (empty($assets)) {
            return;
        }

        /** @var \ReflectionAttribute<Asset> $assetAttr */
        $assetAttr = end($assets);
        /** @var Asset $asset */
        $asset = $assetAttr->newInstance();
        if ($asset->conjoined && $pointer->getAsset()) {
            $this->assetService->remove($this->assetService->get((int) $pointer->getAsset()->getId()));
        }
    }
}
