<?php

namespace Dullahan\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Entity\Asset;
use Dullahan\Entity\UserData;
use Dullahan\Service\ValidationService;
use Dullahan\Trait\PaginationTrait;

/**
 * @extends ServiceEntityRepository<Asset>
 *
 * @method Asset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Asset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Asset[]    findAll()
 * @method Asset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(
        protected ValidationService $validationService,
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Asset::class);
    }

    public function save(Asset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Asset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTakenSpace(UserData $userData): int
    {
        $res = $this->createQueryBuilder('a')
            ->select('sum(a.weight) as space')
            ->where('a.userData = :userData')
            ->setParameter('userData', $userData)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (int) $res;
    }

    public function findByPath(string $path, string $name, string $etx): ?Asset
    {
        return $this->createQueryBuilder('a')
            ->where('a.path = :path')
            ->andWhere('a.name = :name')
            ->andWhere('a.extension = :ext')
            ->setParameter('path', $path)
            ->setParameter('name', $name)
            ->setParameter('ext', $etx)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    /**
    //     * @return Asset[] Returns an array of Asset objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Asset
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
