<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Infrastructure\Mapper\EntityPointersMapper;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\CreateEntity;
use Dullahan\Entity\Presentation\Event\Transport\PersistCreatedEntity;
use Dullahan\Entity\Presentation\Event\Transport\UpdateEntity;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailServiceInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class EntityListener
{
    /** @var array< array<string, Asset>> */
    private array $toSetAssetLater = [];

    public function __construct(
        private EntityManagerInterface $em,
        private ThumbnailServiceInterface $thumbnailService,
    ) {
    }

    #[AsEventListener(event: CreateEntity::class, priority: EventAbstract::PRIORITY_FIRST)]
    public function preAssetAssign(CreateEntity $event): void
    {
        if (!(class_implements($event->class)[AssetAwareInterface::class] ?? false)) {
            return;
        }

        $payload = $event->payload;
        $pointers = $this->getAssetPointerFieldsFromPayload($event->class, $payload);
        if (empty($pointers)) {
            return;
        }

        $fields = [];
        $repo = $this->em->getRepository(Asset::class);
        foreach ($pointers as $fieldName) {
            $asset = $repo->find($payload[$fieldName]);
            if (!$asset) {
                // @TODO specific exception
                throw new \Exception(sprintf('Chosen asset for %s was not found', $fieldName), 400);
            }

            $fields[$fieldName] = $asset;
            unset($payload[$fieldName]);
        }
        $this->toSetAssetLater[] = $fields;
        $event->payload = $payload;
    }

    #[AsEventListener(event: PersistCreatedEntity::class, priority: EventAbstract::PRIORITY_LAST)]
    public function postAssetAssign(PersistCreatedEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $fields = array_pop($this->toSetAssetLater);
        if (!$fields) {
            return;
        }

        foreach ($fields as $fieldName => $asset) {
            $entity->setAsset($fieldName, $asset);
            EntityPointersMapper::setActivePointer($entity, $fieldName);
        }

        $this->em->persist($entity);
        $this->em->flush();

        // @TODO [THUMBNAIL] move to separate event listener at Thumbnail
        foreach ($fields as $fieldName => $asset) {
            $this->thumbnailService->generate($entity, $fieldName);
        }
        $this->thumbnailService->flush();
    }

    #[AsEventListener(event: UpdateEntity::class, priority: EventAbstract::PRIORITY_FIRST)]
    public function preAssetReassign(UpdateEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $payload = $event->payload;
        $pointers = $this->getAssetPointerFieldsFromPayload($entity::class, $payload);
        if (empty($pointers)) {
            return;
        }

        $repo = $this->em->getRepository(Asset::class);
        $fields = [];
        foreach ($pointers as $fieldName) {
            $assetId = $payload[$fieldName];
            $asset = $repo->find($assetId);
            if (!$asset) {
                throw new \Exception(sprintf('Chosen asset for %s was not found', $fieldName), 404);
            }

            $fields[$fieldName] = $asset;
            unset($payload[$fieldName]);
            /** @var ?AssetPointer $oldPointer */
            $oldPointer = $entity->{'get' . ucfirst($fieldName)}();
            if ($oldPointer) {
                if ($oldPointer->getAsset()?->getId() === $asset->getId()) {
                    continue;
                }
                $this->em->remove($oldPointer);
            }

            $entity->setAsset($fieldName, $asset);
        }
        $this->toSetAssetLater[] = $fields;
        $event->payload = $payload;
    }

    #[AsEventListener(event: PersistCreatedEntity::class, priority: EventAbstract::PRIORITY_LAST)]
    public function postAssetReassign(PersistCreatedEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $fields = array_pop($this->toSetAssetLater);
        if (!$fields) {
            return;
        }

        foreach (array_keys($fields) as $fieldName) {
            $this->thumbnailService->generate($entity, $fieldName);
            EntityPointersMapper::setActivePointer($entity, $fieldName);
        }
        $this->thumbnailService->flush();
    }

    /**
     * @param class-string             $class
     * @param array<int|string, mixed> $payload
     *
     * @return array<string>
     */
    private function getAssetPointerFieldsFromPayload(string $class, array $payload): array
    {
        $meta = (array) $this->em->getClassMetadata($class);
        $pointers = [];
        foreach ($meta['associationMappings'] as $mapping) {
            if (AssetPointer::class !== $mapping['targetEntity'] || !isset($payload[$mapping['fieldName']])) {
                continue;
            }

            $pointers[] = $mapping['fieldName'];
        }

        return $pointers;
    }
}
