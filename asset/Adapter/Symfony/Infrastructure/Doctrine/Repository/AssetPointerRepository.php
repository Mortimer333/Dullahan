<?php

namespace Dullahan\Asset\Adapter\Symfony\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AssetPointer>
 *
 * @implements EntityRepositoryInterface<AssetPointer>
 *
 * @method AssetPointer|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetPointer|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetPointer[]    findAll()
 * @method AssetPointer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetPointerRepository extends ServiceEntityRepository implements EntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetPointer::class);
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
