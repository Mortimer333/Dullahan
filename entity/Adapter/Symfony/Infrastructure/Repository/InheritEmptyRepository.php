<?php

namespace Dullahan\Entity\Adapter\Symfony\Infrastructure\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Entity\Domain\Entity\InheritEmpty;

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
}
