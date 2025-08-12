<?php

namespace Dullahan\Thumbnail\Adapter\Symfony\Infrastructure\Database\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Asset\Adapter\Symfony\Infrastructure\Doctrine\Repository\AssetPointerRepository;
use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetPointerInterface;
use Dullahan\Main\Service\RuntimeCachePoolService;
use Dullahan\Thumbnail\Domain\Entity\AssetThumbnailPointer;
use Dullahan\Thumbnail\Domain\Entity\Thumbnail;
use Dullahan\Thumbnail\Domain\Exception\AssetPointNotFoundException;
use Dullahan\Thumbnail\Domain\Exception\ThumbnailEntityNotFoundException;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;
use Dullahan\Thumbnail\Port\Infrastructure\Database\Repository\ThumbnailPersisterInterface;
use Dullahan\Thumbnail\Port\Infrastructure\Database\Repository\ThumbnailRetrieveInterface;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailEntityInterface;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailPointerInterface;

/**
 * @extends ServiceEntityRepository<Thumbnail>
 *
 * @method Thumbnail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thumbnail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thumbnail[]    findAll()
 * @method Thumbnail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThumbnailRepository extends ServiceEntityRepository
implements ThumbnailPersisterInterface, ThumbnailRetrieveInterface
{
    public function __construct(
        ManagerRegistry $registry,
        protected AssetPointerRepository $assetPointerRepository,
        protected AssetPersistenceManagerInterface $assetPersistenceManager,
        protected RuntimeCachePoolService $runtimeCache,
    ) {
        parent::__construct($registry, Thumbnail::class);
    }

    public function save(Thumbnail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Thumbnail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSame(int $assetId, ThumbnailConfig $config): ?ThumbnailEntityInterface
    {
        return $this->findOneBy(['asset' => $assetId, 'settings' => (string) $config]);
    }

    public function getThumbnails(AssetEntityInterface $assetEntity): array
    {
        return $this->findBy(['asset' => $assetEntity->getId()]);
    }

    public function getThumbnailsByPointer(AssetPointerInterface $pointer): array
    {
        $result = $this->createQueryBuilder('t')
            ->innerJoin('t.assetPointers', 'a')
            ->where('a.assetPointer = :pointer')
            ->setParameter('pointer', $pointer->getId())
            ->getQuery()
            ->getResult()
        ;

        if (is_array($result)) {
            return $result;
        }

        return [];
    }

    /**
     * @return array<AssetThumbnailPointer>
     */
    public function getThumbnailsPointers(AssetPointerInterface $pointer): array
    {
        return $this->getEntityManager()
            ->getRepository(AssetThumbnailPointer::class)
            ->findBy(['assetPointer' => $pointer])
        ;
    }

    public function exists(string $path): bool
    {
        try {
            $this->getByPath($path);

            return true;
        } catch (ThumbnailEntityNotFoundException) {
            return false;
        }
    }

    public function get(int $id): ThumbnailEntityInterface
    {
        return $this->uniGet('id', (string) $id);
    }

    public function getByPath(string $path): ThumbnailEntityInterface
    {
        return $this->uniGet('path', $path);
    }

    protected function uniGet(string $type, string $id): ThumbnailEntityInterface
    {
        $item = $this->runtimeCache->getItem(implode('_', ['thumbnail', $type, $id]));
        if ($item->isHit()) {
            $entity = $item->get();
            if ($entity instanceof ThumbnailEntityInterface) {
                return $entity;
            }
        }

        $asset = $this->getEntityManager()->getRepository(Thumbnail::class)->findOneBy([$type => $id]);
        if (!$asset) {
            throw new ThumbnailEntityNotFoundException(sprintf('Thumbnail [%s] not found', $id));
        }

        $item->set($asset);
        $this->runtimeCache->save($item);

        return $asset;
    }

    public function createPointer(
        ThumbnailEntityInterface $thumbnail,
        int $pointerId,
        string $code,
    ): ThumbnailPointerInterface {
        $pointer = $this->assetPointerRepository->find($pointerId);
        if (!$pointer) {
            throw new AssetPointNotFoundException($pointerId);
        }

        if ($id = $thumbnail->getId()) {
            $thumbnail = $this->find($id);
        }

        if (!$thumbnail instanceof Thumbnail) {
            throw new \InvalidArgumentException(
                'Passed object for thumbnail pointer creation wasn\'t an Thumbnail Entity nor had it\'s ID',
                422,
            );
        }

        $assetThumbnailPointer = new AssetThumbnailPointer();
        $assetThumbnailPointer->setAssetPointer($pointer)
            ->setThumbnail($thumbnail)
            ->setCode($code)
        ;
        $this->getEntityManager()->persist($assetThumbnailPointer);

        return $assetThumbnailPointer;
    }

    public function create(
        AssetEntityInterface $asset,
        string $path,
        string $filename,
        $thumbFileHandle,
        ThumbnailConfig $config,
    ): ThumbnailEntityInterface {
        if (!is_resource($thumbFileHandle)) {
            throw new \Exception('Placeholder');
        }

        $thumbnail = new Thumbnail();
        $assetEntity = $this->getEntityManager()
            ->getRepository(\Dullahan\Asset\Domain\Entity\Asset::class)
            ->find($asset->getId())
        ;

        $thumbnail->setAsset($assetEntity)
            ->setPath($path)
            ->setName($filename)
            ->setWeight(fstat($thumbFileHandle)['size'] ?? 0)
            ->setSettings((string) $config)
        ;

        /** @var AssetThumbnailPointer $assetThumbnailPointer */
        $assetThumbnailPointer = $this->createPointer($thumbnail, $config->pointerId, $config->code);
        $thumbnail->addAssetPointer($assetThumbnailPointer);
        $this->getEntityManager()->persist($thumbnail);

        return $thumbnail;
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function removeThumbnailsFromPointer(AssetPointerInterface $pointer): void
    {
        foreach ($this->getThumbnailsPointers($pointer) as $thumbnailPointer) {
            $this->getEntityManager()->remove($thumbnailPointer);
            $thumbnail = $thumbnailPointer->getThumbnail();
            if (!$thumbnail) {
                continue;
            }

            $thumbnail->removeAssetPointer($thumbnailPointer);
            if (0 === count($thumbnail->getAssetPointers())) {
                $this->getEntityManager()->remove($thumbnail);
            }
        }
    }
}
