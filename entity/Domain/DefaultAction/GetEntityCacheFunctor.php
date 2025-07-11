<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Domain\Enum\EntityCacheCastEnum;
use Psr\Cache\CacheItemPoolInterface;

class GetEntityCacheFunctor
{
    public function __construct(
        protected CacheItemPoolInterface $cache,
    ) {
    }

    public function __invoke(string $key, string $cast = EntityCacheCastEnum::NONE->value): mixed
    {
        $item = $this->cache->getItem($key);
        if (!$item->isHit()) {
            return null;
        }

        $cache = $item->get();

        return match (EntityCacheCastEnum::tryFrom($cast)) {
            EntityCacheCastEnum::JSON_OBJECT => json_decode($cache),
            EntityCacheCastEnum::JSON_ARRAY => json_decode($cache, true),
            EntityCacheCastEnum::INT => (int) $cache,
            EntityCacheCastEnum::FLOAT => (float) $cache,
            EntityCacheCastEnum::ARRAY => (array) $cache,
            EntityCacheCastEnum::BOOL => (bool) $cache,
            EntityCacheCastEnum::OBJECT => (object) $cache,
            EntityCacheCastEnum::NONE => $cache,
            default => $cache,
        };
    }
}
