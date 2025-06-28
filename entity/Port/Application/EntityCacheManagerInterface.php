<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Application;

/**
 * @template T of object
 */
interface EntityCacheManagerInterface
{
    /**
     * @param class-string<T> $class
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function removeCacheById(string $class, int $id): void;

    /**
     * @param T $entity
     */
    public function removeCache(object $entity): void;

    /**
     * @param T            $entity
     * @param array<mixed> $definition
     */
    public function removeRelatedCache(object $entity, array $definition): void;
}
