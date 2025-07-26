<?php

declare(strict_types=1);

namespace Dullahan\Main\Symfony;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Port\Infrastructure\EntityRepositoryInterface;
use Dullahan\Main\Contract\DatabaseActionsInterface;
use Dullahan\Main\Contract\DatabaseConnectionInterface;

/**
 * @implements DatabaseConnectionInterface<Connection>
 */
final class DoctrineDatabaseActionsImpl implements DatabaseActionsInterface, DatabaseConnectionInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function getRepository(string $className): ?EntityRepositoryInterface
    {
        $repository = $this->em->getRepository($className);

        if (!$repository instanceof EntityRepositoryInterface) {
            return null;
        }

        return $repository;
    }

    public function beginTransaction(): void
    {
        $this->em->beginTransaction();
    }

    public function commit(): void
    {
        $this->em->commit();
    }

    public function rollback(): void
    {
        $this->em->rollback();
    }

    public function getConnection(): object
    {
        return $this->em->getConnection();
    }
}
