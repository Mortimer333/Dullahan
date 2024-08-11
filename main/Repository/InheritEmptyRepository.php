<?php

namespace Dullahan\Main\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Main\Entity\InheritEmpty;

/**
 * @extends ServiceEntityRepository<InheritEmpty>
 *
 * @method InheritEmpty|null find($id, $lockMode = null, $lockVersion = null)
 * @method InheritEmpty|null findOneBy(array $criteria, array $orderBy = null)
 * @method InheritEmpty[]    findAll()
 * @method InheritEmpty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InheritEmptyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InheritEmpty::class);
    }

    public function save(InheritEmpty $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InheritEmpty $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return InheritEmpty[] Returns an array of InheritEmpty objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?InheritEmpty
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
