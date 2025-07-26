<?php

namespace Dullahan\Asset\Adapter\Symfony\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Entity\Adapter\Symfony\Domain\Trait\PaginationTrait;
use Dullahan\Entity\Port\Domain\EntityValidationInterface;
use Dullahan\Entity\Port\Infrastructure\EntityRepositoryInterface;
use Dullahan\User\Domain\Entity\UserData;

/**
 * @extends ServiceEntityRepository<Asset>
 *
 * @implements EntityRepositoryInterface<Asset>
 *
 * @method Asset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Asset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Asset[]    findAll()
 * @method Asset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetRepository extends ServiceEntityRepository implements EntityRepositoryInterface
{
    use PaginationTrait;

    public function __construct(
        protected EntityValidationInterface $validationService,
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Asset::class);
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

    public function findByPath(string $directory, string $name, string $etx): ?Asset
    {
        return $this->createQueryBuilder('a')
            ->where('a.directory = :directory')
            ->andWhere('a.name = :name')
            ->andWhere('a.extension = :ext')
            ->setParameter('directory', $directory)
            ->setParameter('name', $name)
            ->setParameter('ext', $etx)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
