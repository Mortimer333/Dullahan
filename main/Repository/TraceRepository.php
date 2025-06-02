<?php

namespace Dullahan\Main\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Entity\Adapter\Symfony\Domain\Trait\PaginationTrait;
use Dullahan\Entity\Port\Domain\EntityValidationInterface;
use Dullahan\Main\Entity\Trace;

/**
 * @extends ServiceEntityRepository<Trace>
 *
 * @method Trace|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trace|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trace[]    findAll()
 * @method Trace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TraceRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(
        protected EntityValidationInterface $validationService,
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Trace::class);
    }

    public function save(Trace $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Trace $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
