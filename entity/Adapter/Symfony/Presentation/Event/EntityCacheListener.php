<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Domain\DefaultAction\CacheEntityRelatedFunctor;
use Dullahan\Entity\Domain\DefaultAction\CacheRemoveRelatedFunctor;
use Dullahan\Entity\Domain\DefaultAction\GetEntityCacheFunctor;
use Dullahan\Entity\Presentation\Event\Transport\CacheEntity;
use Dullahan\Entity\Presentation\Event\Transport\CacheRemoveRelated;
use Dullahan\Entity\Presentation\Event\Transport\GetEntityCache;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @template T
 */
class EntityCacheListener
{
    public function __construct(
        protected GetEntityCacheFunctor $getEntityCache,
        protected CacheEntityRelatedFunctor $cacheEntityRelated,
        protected CacheRemoveRelatedFunctor $cacheRemoveRelatedFunctor,
    ) {
    }

    /**
     * @param GetEntityCache<T> $event
     */
    #[AsEventListener(event: GetEntityCache::class)]
    public function onGetEntityCache(GetEntityCache $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->cached = ($this->getEntityCache)($event->key, $event->cast);
        $event->isHit = !is_null($event->cached);
    }

    #[AsEventListener(event: CacheEntity::class)]
    public function onCacheEntity(CacheEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        ($this->cacheEntityRelated)($event->key, $event->toCache, $event->expiry);
    }

    #[AsEventListener(event: CacheRemoveRelated::class)]
    public function onCacheRemoveRelated(CacheRemoveRelated $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        ($this->cacheRemoveRelatedFunctor)($event);
    }
}
