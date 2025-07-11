<?php

namespace Dullahan\Thumbnail\Adapter\Symfony\Infrastructure\Database\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Thumbnail\Domain\Entity\AssetThumbnailPointer;

/**
 * @extends ServiceEntityRepository<AssetThumbnailPointer>
 *
 * @method AssetThumbnailPointer|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetThumbnailPointer|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetThumbnailPointer[]    findAll()
 * @method AssetThumbnailPointer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetThumbnailPointerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetThumbnailPointer::class);
    }

    public function save(AssetThumbnailPointer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AssetThumbnailPointer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
