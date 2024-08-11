<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\AssetManager\Jackrabbit;

use Dullahan\Main\Document\JackrabbitAsset;

/**
 * Variation of PSR-6 CacheItemPoolInterface
 * Not implemented methods:
 * - expiresAt && expiresAfter - expiry time is only one: at the end of script.
 */
class JackrabbitRuntimeCacheItem
{
    public function __construct(
        protected string $key,
        protected ?JackrabbitAsset $subject = null,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): ?JackrabbitAsset
    {
        return $this->subject;
    }

    public function isHit(): bool
    {
        return (bool) $this->subject;
    }

    public function set(JackrabbitAsset $asset): self
    {
        $this->subject = $asset;

        return $this;
    }
}
