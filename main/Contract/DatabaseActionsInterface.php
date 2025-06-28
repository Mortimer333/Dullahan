<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;

interface DatabaseActionsInterface
{
    /**
     * @param class-string<T> $className
     *
     * @return EntityRepositoryInterface<T>|null
     *
     * @template T of object
     */
    public function getRepository(string $className): ?EntityRepositoryInterface;

    /**
     * Starts a transaction on the underlying database connection.
     */
    public function beginTransaction(): void;

    /**
     * Commits a transaction on the underlying database connection.
     */
    public function commit(): void;

    /**
     * Performs a rollback on the underlying database connection.
     */
    public function rollback(): void;
}
