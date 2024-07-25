<?php

declare(strict_types=1);

namespace Dullahan\Service\AssetManager\Jackrabbit;

use Dullahan\Model\AssetManager\Jackrabbit\JackrabbitRuntimeCacheItem;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * Variation of PSR-6 CacheItemPoolInterface
 * It allows only one type of value: Jackrabbit node proxy Asset, and has automatic expiry at script end (runtime)
 * Not implemented methods:
 * - saveDeferred & commit - there is no "later"
 */
class JackrabbitRuntimeCachePoolService
{
    /**
     * @var array<string, JackrabbitRuntimeCacheItem>
     */
    protected array $pool = [];

    public function getItem(string $key): JackrabbitRuntimeCacheItem
    {
        if (!isset($pool[$key])) {
            $pool[$key] = new JackrabbitRuntimeCacheItem(
                $key,
            );
        }

        return $pool[$key];
    }

    public function getItems(array $keys = array()): \Traversable
    {
        foreach ($keys as $key) {
            yield $this->getItem($key);
        }
    }

    public function hasItem($key) {
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

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            unset($this->pool[$key]);
        }

        return true;
    }

    public function save(JackrabbitRuntimeCacheItem $item): bool
    {
        $asset = $item->get();
        if (!$asset) {
            return false;
        }

        $assetId = $asset->getId();
        if ($assetId) {
            $this->pool[(string) $assetId] = $item;
        }

        $path = $asset->getPath();
        if ($path) {
            $this->pool[$path] = $item;
        }

        return true;
    }

    public function traverse(): \Iterator
    {
        foreach ($this->pool as $item) {
            yield $item;
        }
    }
}
