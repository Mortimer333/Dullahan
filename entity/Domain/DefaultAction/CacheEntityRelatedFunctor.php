<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Psr\Cache\CacheItemPoolInterface;

class CacheEntityRelatedFunctor
{
    public function __construct(
        protected CacheItemPoolInterface $cache,
    ) {
    }

    public function __invoke(string $key, string $toCache, int|\DateInterval|null $expires): void
    {
        $item = $this->cache->getItem($key);
        // @TODO expires after should be a parameter
        $item->set($toCache)->expiresAfter($expires);
        $this->cache->save($item);
    }
}
