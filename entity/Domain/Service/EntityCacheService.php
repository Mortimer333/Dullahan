<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Service;

use Dullahan\Entity\Port\Domain\EntityCacheServiceInterface;
use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Main\Contract\DatabaseActionsInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @TODO add events when cache is removed and handle InheritanceAwareInterface there
 * @TODO move $_ENV into constructor
 */
class EntityCacheService implements EntityCacheServiceInterface
{
    public const NO_CACHE = 'cache:empty';
    public const CACHE_SEPARATOR = '_';

    public function __construct(
        protected CacheItemPoolInterface $cache,
        protected DatabaseActionsInterface $databaseConnection,
    ) {
    }

    public function getCacheSeparator(): string
    {
        return self::CACHE_SEPARATOR;
    }

    public function deleteEntityCache(object $entity, bool $inherit = false): void
    {
        $this->cache->deleteItem($this->getEntitySerializedCacheKey($entity, $inherit));
        if ($entity instanceof InheritanceAwareInterface) {
            foreach ($entity->getChildren() as $child) {
                $this->deleteEntityCache($child, true);
            }
        }
    }

    public function deleteCacheById(int $id, string $class, bool $inherit = false): void
    {
        $this->cache->deleteItem($this->getSerializedCacheKey($id, $class, $inherit));
        if (class_implements($class)[InheritanceAwareInterface::class] ?? false) {
            $entity = $this->databaseConnection->getRepository($class)?->find($id);
            if (!$entity || !$entity instanceof InheritanceAwareInterface) {
                return;
            }

            foreach ($entity->getChildren() as $child) {
                $this->deleteEntityCache($child, true);
            }
        }
    }

    public function getEntitySerializedCacheKey(object $entity, bool $inherit = false): string
    {
        $id = method_exists($entity, 'getId') ? $entity->getId() : null;
        if (is_null($id)) {
            return self::NO_CACHE;
        }

        return $this->getSerializedCacheKey($id, $entity::class, $inherit);
    }

    public function getSerializedCacheKey(int $id, string $class, bool $inherit): string
    {
        return $this->joinVars(
            'class',
            $this->getCacheClass($class),
            $id,
            $inherit ? '1' : '0',
        );
    }

    public function getEntityFieldCacheKey(string $class): string
    {
        return $this->joinVars('class', $this->getCacheClass($class), 'field');
    }

    public function getEntityDefinitionCacheKey(string $class): string
    {
        return $this->joinVars('class', $this->getCacheClass($class), 'definition');
    }

    public function getCacheClass(string $class): string
    {
        return str_replace('\\', '-', str_replace('Proxies\__CG__\\', '', $class));
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    protected function joinVars(string|int|float ...$vars): string
    {
        return implode($this->getCacheSeparator(), [$_ENV['APP_ENV'], ...$vars]);
    }
}
