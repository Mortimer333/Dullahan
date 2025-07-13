<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Application;

use Dullahan\Entity\Domain\Exception\EntityNotFoundException;
use Dullahan\Entity\Domain\Exception\EntityValidationException;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;

interface EntityPersistManagerInterface
{
    /**
     * @param class-string             $class
     * @param array<int|string, mixed> $payload
     *
     * @throws EntityValidationException
     */
    public function create(string $class, array $payload, bool $flush = true): IdentityAwareInterface;

    /**
     * @param class-string             $class
     * @param array<int|string, mixed> $payload
     *
     * @throws EntityValidationException
     * @throws EntityNotFoundException
     */
    public function update(string $class, int $id, array $payload, bool $flush = true): IdentityAwareInterface;

    /**
     * @param class-string $class
     *
     * @throws EntityNotFoundException
     */
    public function remove(string $class, int $id, bool $flush = true): bool;
}
