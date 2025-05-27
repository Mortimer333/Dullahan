<?php

namespace Dullahan\Asset\Adapter\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Asset\Domain\Entity\AssetPointer;

/**
 * @extends ServiceEntityRepository<AssetPointer>
 *
 * @method AssetPointer|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetPointer|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetPointer[]    findAll()
 * @method AssetPointer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetPointerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetPointer::class);
    }

    public function save(AssetPointer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AssetPointer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
