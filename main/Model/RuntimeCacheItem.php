<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

/**
 * Variation of PSR-6 CacheItemPoolInterface
 * Not implemented methods:
 * - expiresAt && expiresAfter - expiry time is only one: at the end of script.
 */
class RuntimeCacheItem
{
    public function __construct(
        protected string $key,
        protected ?object $subject = null,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): ?object
    {
        return $this->subject;
    }

    public function isHit(): bool
    {
        return (bool) $this->subject;
    }

    public function set(object $asset): self
    {
        $this->subject = $asset;

        return $this;
    }
}
