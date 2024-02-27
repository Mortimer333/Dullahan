<?php

declare(strict_types=1);

namespace Dullahan\Service;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Contract\InheritanceAwareInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheService
{
    public const NO_CACHE = 'no:cache';

    public function __construct(
        protected CacheItemPoolInterface $cache,
        protected EntityManagerInterface $em,
    ) {
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

    /**
     * @param class-string $class
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteCacheById(int $id, string $class, bool $inherit = false): void
    {
        $this->cache->deleteItem($this->getSerializedCacheKey($id, $class, $inherit));
        if (class_implements($class)[InheritanceAwareInterface::class] ?? false) {
            $entity = $this->em->getRepository($class)->find($id);
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
        return $_ENV['APP_ENV'] . ':class:' . $this->getCacheClass($class) . ':' . $id . ':'
            . ($inherit ? '1' : '0');
    }

    public function getEntityFieldCacheKey(string $class): string
    {
        return $_ENV['APP_ENV'] . ':class:' . $this->getCacheClass($class) . ':field';
    }

    public function getEntityDefinitionCacheKey(string $class): string
    {
        return $_ENV['APP_ENV'] . ':class:' . $this->getCacheClass($class) . ':definition';
    }

    public function getCacheClass(string $class): string
    {
        return str_replace('\\', '-', str_replace('Proxies\__CG__\\', '', $class));
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }
}
