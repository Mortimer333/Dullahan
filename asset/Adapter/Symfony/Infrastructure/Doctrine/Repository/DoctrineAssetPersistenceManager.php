<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Asset\Domain\Exception\AssetEntityNotFoundException;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Main\Service\RuntimeCachePoolService;
use Dullahan\User\Domain\Entity\User;

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
        // @TODO this is actually wrong - if we remove them via sql then all event structure will fail.
        //      What about thumbnails, what about asset pointers?
        $qb = $this->em->createQueryBuilder();
        $likePath = rtrim($asset->getFullPath(), '/') . '/%';
        $query = $qb->delete(Asset::class, 'a')
            ->where('a.fullPath LIKE :path')
            ->setParameter('path', $likePath)
            ->getQuery()
        ;
        $query->execute();
        // Clearing whole runtime cache after removal of several assets, as it is quicker and easier to do.
        // Otherwise, we would have to iterate over all items and generate key for each of them and in the end,
        // clear it anyway, after the request has ended.
        $this->runtimeCache->clear();
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
        // @TODO this is a very stupid solution - we are updating dependencies before the actual parent is updated
        //      and outside of the transaction - if something fails we have corrupted our database
        //      Our reasoning behind saving fullpath and directory in the db is to make searching easier,
        //      But we could achive the same using View with calculated values and inner joining it with Assets.
        //      Possible solution: make directories and fullpaths a separate table and move this resposibilities to
        //      MySql trigger - https://dev.mysql.com/doc/refman/8.4/en/trigger-syntax.html.
        if ($asset->getId()) {
            $this->updateChildrenFullPaths($asset, $structure);
        }

        $asset->setModified(new \DateTime());
        $asset->setFullPath($structure->path);
        $asset->setDirectory(dirname($structure->path));
        $asset->setName($structure->name);
        $asset->setWeight($structure->weight);
        $asset->setExtension($structure->extension);
        $asset->setMimeType($structure->mimeType);
        $asset->setHidden('.' === ($asset->getName()[0] ?? null));
    }

    private function updateChildrenFullPaths(Asset $asset, Structure $structure): void
    {
        $genMainQuery = fn () => $this->em->createQueryBuilder()
            ->update(Asset::class, 'a')
            ->set('a.fullPath', 'REPLACE(a.fullPath, :oldPath, :pathToReplace)')
            ->set('a.directory', 'REPLACE(a.directory, :oldPath, :pathToReplace)')
            ->where('a.fullPath LIKE :oldPathLike')
            ->orWhere('a.fullPath = :oldPathFull')
            ->setParameter('pathToReplace', rtrim($structure->path, '/'))
            ->setParameter('oldPath', rtrim($asset->getFullPath(), '/'))
            ->setParameter('oldPathFull', rtrim($asset->getFullPath(), '/'))
            ->setParameter('oldPathLike', rtrim($asset->getFullPath(), '/') . '/%')
        ;

        $query = $genMainQuery()
            ->andWhere('a.extension IS NULL')
            ->getQuery()
        ;
        $query->execute();

        $query = $genMainQuery()
            ->andWhere('a.extension IS NOT NULL')
            ->getQuery()
        ;
        $query->execute();
    }
}
