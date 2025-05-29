<?php

namespace Dullahan\User\Adapter\Symfony\Infrastructure\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Dullahan\Main\Trait\PaginationTrait;
use Dullahan\User\Domain\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use PaginationTrait;

    public const ALL = 'all';
    public const ACTIVE = 'active';
    public const NOT_VALIDATED = 'not_validated';
    public const DEACTIVATED = 'deactivated';
    public const ENTITY_CLASS = User::class;

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function findUniqueEmail(string $email): ?User
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->where(
            $qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->eq('u.newEmail', ':email'),
                    $qb->expr()->gte('u.emailVerificationTokenExp', time()),
                ),
                $qb->expr()->eq('u.email', ':email')
            )
        )->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getUsersCount(string $type = self::ALL, ?int $from = null, ?int $to = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
        ;

        if (self::ALL !== $type) {
            match ($type) {
                self::ACTIVE => $qb->where('u.activated = 1'),
                self::NOT_VALIDATED => $qb->where('u.activated = 0')->andWhere('u.activationToken IS NOT NULL'),
                self::DEACTIVATED => $qb->where('u.activated = 0')->andWhere('u.activationToken IS NULL'),
                default => null,
            };
        }

        if ($from) {
            $qb->andWhere('u.created >= :from')->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('u.created <= :to')->setParameter('to', $to);
        }

        return (int) $qb->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
