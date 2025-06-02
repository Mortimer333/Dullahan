<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Application\Manager\FileSystemBasedAssetManager;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Infrastructure\Mapper\EntityPointersMapper;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Entity\Adapter\Symfony\Domain\EntityUtilService;
use Dullahan\Entity\Presentation\Event\Transport\PostCreate;
use Dullahan\Entity\Presentation\Event\Transport\PostUpdate;
use Dullahan\Entity\Presentation\Event\Transport\PreCreate;
use Dullahan\Entity\Presentation\Event\Transport\PreUpdate;
use Dullahan\Main\Service\CacheService;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailServiceInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class EntityListener
{
    /** @var array< array<string, Asset>> */
    protected array $toSetAssetLater = [];

    public function __construct(
        protected EntityManagerInterface $em,
        protected EntityUtilService $entityUtilService,
        protected FileSystemBasedAssetManager $assetService,
        protected CacheService $cacheService,
        protected ThumbnailServiceInterface $thumbnailService,
    ) {
    }

    #[AsEventListener(event: PreCreate::class, priority: -256)]
    public function preAssetAssign(PreCreate $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $payload = $event->getPayload();
        $pointers = $this->getAssetPointerFieldsFromPayload($entity, $payload);
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
        $event->setPayload($payload);
    }

    #[AsEventListener(event: PostCreate::class, priority: -256)] // Should be called last
    public function postAssetAssign(PostCreate $event): void
    {
        $entity = $event->getEntity();
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

    #[AsEventListener(event: PreUpdate::class, priority: -256)] // Should be called last
    public function preAssetReassign(PreUpdate $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof AssetAwareInterface) {
            return;
        }

        $payload = $event->getPayload();
        $pointers = $this->getAssetPointerFieldsFromPayload($entity, $payload);
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
        $event->setPayload($payload);
    }

    #[AsEventListener(event: PostUpdate::class, priority: -256)] // Should be called last
    public function postAssetReassign(PostUpdate $event): void
    {
        $entity = $event->getEntity();
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
     * @param array<string, mixed> $payload
     *
     * @return array<string>
     */
    protected function getAssetPointerFieldsFromPayload(AssetAwareInterface $entity, array $payload): array
    {
        $meta = (array) $this->em->getClassMetadata($entity::class);
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
