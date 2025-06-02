<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;
use Dullahan\User\Domain\Entity\User;
use Psr\Cache\InvalidArgumentException; // @TODO change it to Dullahan InvalidArgumentException and wrap Doctrine exceptions

interface EntityServiceInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws \Exception
     */
    public function get(string $class, int $id): object;

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return EntityRepositoryInterface<T>
     */
    public function getRepository(string $class): EntityRepositoryInterface;

    public function enableOwnershipCheck(): void;

    public function disableOwnershipCheck(): void;

    /**
     * @param array<string, mixed>|null $dataSet
     *
     * @return array<string, mixed>
     */
    public function serialize(
        object $entity,
        ?array $dataSet = null,
        bool $inherit = true
    ): array;

    /**
     * @return array<mixed>
     *
     * @throws InvalidArgumentException
     */
    public function getEntityDefinition(object $entity): array;

    /**
     *  @template T of object
     *
     * @param class-string<T>          $class
     * @param array<int|string, mixed> $payload
     *
     * @return T
     */
    public function create(string $class, array $payload, bool $flush = true): object;

    /**
     * @param class-string             $class
     * @param array<int|string, mixed> $payload
     */
    public function update(string $class, int $id, array $payload, bool $persist = true): object;

    /**
     * @param class-string $class
     *
     * @throws \Exception
     */
    public function remove(string $class, int $id): void;

    public function logout(): void;

    public function login(User $user): void;

    /**
     * @param array<mixed> $definition
     */
    public function clearRelatedCache(object $entity, array $definition): void;

    public function removeEntityCache(object $entity): void;

    /**
     * @param class-string $class
     *
     * @throws InvalidArgumentException
     */
    public function removeCacheById(int $id, string $class): void;

    /**
     * @return class-string
     */
    public function getEntityTrueClass(object $entity): string;
}
