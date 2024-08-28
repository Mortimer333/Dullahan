<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Application\Exception\AssetEntityNotFoundException;
use Dullahan\Asset\Application\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Entity\Asset;
use Dullahan\Main\Entity\User;
use Dullahan\Main\Service\RuntimeCachePoolService;

class DoctrineAssetPersistenceManager implements AssetPersistenceManagerInterface
{
    public function __construct(
        protected RuntimeCachePoolService $runtimeCache,
        protected EntityManagerInterface $em,
    ) {
    }

    public function list(
        int $limit,
        int $offset,
        ?array $sort = null,
        ?array $filter = null,
        ?array $join = null,
        ?array $group = null,
    ): array {
        /** @var array<AssetEntityInterface> $assets */
        $assets = $this->em->getRepository(Asset::class)->list(
            [
                'limit' => $limit,
                'offset' => $offset,
                'sort' => $sort,
                'filter' => $filter,
                'join' => $join,
                'group' => $group,
            ],
        );

        return $assets;
    }

    public function count(
        ?array $sort = null,
        ?array $filter = null,
        ?array $join = null,
        ?array $group = null,
    ): int {
        return $this->em->getRepository(Asset::class)->total(
            [
                'sort' => $sort,
                'filter' => $filter,
                'join' => $join,
                'group' => $group,
            ],
        );
    }

    public function exists(string $path): bool
    {
        try {
            $this->uniGet('fullPath', rtrim($path, '/'));

            return true;
        } catch (AssetEntityNotFoundException) {
            return false;
        }
    }

    public function get(int $id): AssetEntityInterface
    {
        return $this->uniGet('id', (string) $id);
    }

    public function getByPath(string $path): AssetEntityInterface
    {
        return $this->uniGet('fullPath', $path);
    }

    public function create(Structure $structure, User $owner): AssetEntityInterface
    {
        $asset = new Asset();
        $asset->setUser($owner);
        $this->updateEntity($asset, $structure);
        $this->em->persist($asset);

        return $asset;
    }

    public function update(AssetEntityInterface $asset, Structure $structure): void
    {
        if ($asset instanceof Asset) {
            $this->updateEntity($asset, $structure);
        }
    }

    public function remove(AssetEntityInterface $asset): void
    {
        $this->em->remove($asset);
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    public function clear(): void
    {
        $this->em->clear();
    }

    protected function uniGet(string $type, string $id): AssetEntityInterface
    {
        $item = $this->runtimeCache->getItem(implode('_', ['asset', $type, $id]));
        if ($item->isHit()) {
            $entity = $item->get();
            if ($entity instanceof AssetEntityInterface) {
                return $entity;
            }
        }

        $asset = $this->em->getRepository(Asset::class)->findOneBy([$type => $id]);
        if (!$asset) {
            throw new AssetEntityNotFoundException(sprintf('Asset [%s] not found', $id));
        }

        $item->set($asset);
        $this->runtimeCache->save($item);

        return $asset;
    }

    protected function updateEntity(
        Asset $asset,
        Structure $structure,
    ): void {
        $asset->setFullPath($structure->path);
        $asset->setDirectory(dirname($structure->path));
        $asset->setName($structure->name);
        $asset->setWeight($structure->weight);
        $asset->setExtension($structure->extension);
        $asset->setMimeType($structure->mimeType);
        $asset->setHidden('.' === ($asset->getName()[0] ?? null));
    }
}
