<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Dullahan\Main\Model\RuntimeCacheItem;

/**
 * @internal
 *
 * Variation of PSR-6 CacheItemPoolInterface
 * Not implemented methods:
 * - saveDeferred & commit - there is no "later"
 */
class RuntimeCachePoolService
{
    /**
     * @var array<string, RuntimeCacheItem>
     */
    protected array $pool = [];

    public function getItem(string $key): RuntimeCacheItem
    {
        if (!isset($this->pool[$key])) {
            $this->pool[$key] = new RuntimeCacheItem($key);
        }

        return $this->pool[$key];
    }

    /**
     * @param array<string> $keys
     *
     * @return \Traversable<RuntimeCacheItem>
     */
    public function getItems(array $keys = []): \Traversable
    {
        foreach ($keys as $key) {
            yield $this->getItem($key);
        }
    }

    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function clear(): bool
    {
        $this->pool = [];

        return true;
    }

    public function deleteItem(string $key): bool
    {
        unset($this->pool[$key]);

        return true;
    }

    /**
     * @param array<string> $keys
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            unset($this->pool[$key]);
        }

        return true;
    }

    public function save(RuntimeCacheItem $item): bool
    {
        $asset = $item->get();
        if (!$asset) {
            return false;
        }

        // @TODO refactor this, is it necessary?
        $assetId = method_exists($asset, 'getId') ? $asset->getId() : false;
        if ($assetId) {
            $this->pool[(string) $assetId] = $item;
        }

        $path = method_exists($asset, 'getPath') ? $asset->getPath() : false;
        if ($path) {
            $this->pool[(string) $path] = $item;
        }

        return true;
    }

    /**
     * @return \Iterator<RuntimeCacheItem>
     */
    public function traverse(): \Iterator
    {
        foreach ($this->pool as $item) {
            yield $item;
        }
    }
}
