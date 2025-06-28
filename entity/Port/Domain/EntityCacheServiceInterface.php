<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

use Psr\Cache\InvalidArgumentException;

interface EntityCacheServiceInterface
{
    public function deleteEntityCache(object $entity, bool $inherit = false): void;

    /**
     * @param class-string $class
     *
     * @throws InvalidArgumentException
     */
    public function deleteCacheById(int $id, string $class, bool $inherit = false): void;

    public function getEntitySerializedCacheKey(object $entity, bool $inherit = false): string;

    /**
     * @param class-string $class
     */
    public function getSerializedCacheKey(int $id, string $class, bool $inherit): string;

    /**
     * @param class-string $class
     */
    public function getEntityFieldCacheKey(string $class): string;

    /**
     * @param class-string $class
     */
    public function getEntityDefinitionCacheKey(string $class): string;

    /**
     * @param class-string $class
     */
    public function getCacheClass(string $class): string;
}
