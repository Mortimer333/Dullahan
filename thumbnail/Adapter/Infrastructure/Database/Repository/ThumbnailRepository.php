<?php

namespace Dullahan\Thumbnail\Adapter\Infrastructure\Database\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DullahanMainContract\AssetManager\AssetInterface;
use Dullahan\Thumbnail\Adapter\Infrastructure\Database\Entity\Thumbnail;
use Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository\ThumbnailRetrieveInterface;
use Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository\ThumbnailStoreInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailConfigInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailInterface;

/**
 * @extends ServiceEntityRepository<Thumbnail>
 *
 * @method Thumbnail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thumbnail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thumbnail[]    findAll()
 * @method Thumbnail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThumbnailRepository extends ServiceEntityRepository implements ThumbnailStoreInterface, ThumbnailRetrieveInterface
{
    public function __construct(ManagerRegistry $registry)
    {
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

    public function findSame(AssetInterface $asset, ThumbnailConfigInterface $config): ThumbnailInterface|null
    {
        return $this->findOneBy(['asset' => $asset, 'settings' => (string) $config]);
    }
}
