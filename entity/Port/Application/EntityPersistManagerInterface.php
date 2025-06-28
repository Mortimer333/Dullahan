<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Application;

use Dullahan\Entity\Domain\Exception\EntityNotFoundException;
use Dullahan\Entity\Domain\Exception\EntityValidationException;

/**
 * @template T of object
 */
interface EntityPersistManagerInterface
{
    /**
     * @param class-string<T>          $class
     * @param array<int|string, mixed> $payload
     *
     * @return T
     *
     * @throws EntityValidationException
     */
    public function create(string $class, array $payload, bool $flush = true): object;

    /**
     * @param class-string<T>          $class
     * @param array<int|string, mixed> $payload
     *
     * @return T
     *
     * @throws EntityValidationException
     * @throws EntityNotFoundException
     */
    public function update(string $class, int $id, array $payload, bool $flush = true): object;

    /**
     * @param class-string<T> $class
     *
     * @throws EntityNotFoundException
     */
    public function delete(string $class, int $id, bool $flush = true): bool;
}
