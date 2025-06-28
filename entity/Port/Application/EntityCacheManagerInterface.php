<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Application;

interface EntityCacheManagerInterface
{
    /**
     * @param class-string $class
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function removeCacheById(string $class, int $id): void;

    public function removeCache(object $entity): void;

    /**
     * @param array<mixed> $definition
     */
    public function removeRelatedCache(object $entity, array $definition): void;
}
