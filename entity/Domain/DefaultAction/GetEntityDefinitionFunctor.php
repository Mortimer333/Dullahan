<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Adapter\Symfony\Domain\Reader\FieldReader;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Domain\EntityCacheServiceInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @template T of object
 *
 * @phpstan-import-type EntityDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class GetEntityDefinitionFunctor
{
    /**
     * @param EntityDefinitionManagerInterface<T> $entityDefinitionManager
     */
    public function __construct(
        protected EntityCacheServiceInterface $entityCacheService,
        protected CacheItemPoolInterface $cache,
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
    ) {
    }

    /**
     * @TODO this should be decoupled into three phases: Cache Retrieve, Generate definition, Cache save
     *
     * @param T $entity
     *
     * @return EntityDefinition
     */
    public function __invoke(object $entity): ?array
    {
        $item = $this->cache->getItem($this->entityCacheService->getEntityDefinitionCacheKey($entity::class));
        if ($item->isHit()) {
            return json_decode($item->get(), true);
        }

        $trueClassName = $this->entityDefinitionManager->getEntityTrueClass($entity);
        if (!$trueClassName) {
            return null;
        }

        // @TODO Why is this encoded and decoded at the same time?
        $definition = json_decode(
            json_encode(
                // @TODO caching inside the FileReader? Maybe something that we can move?
                // @TODO what about refactoring this to more like Symfony Serializer? It would be great if we could
                //      pass our own serialization modules or replace default one
                (new FieldReader($entity, $this->entityCacheService, $this->cache))
                    ->getFields($trueClassName)
            ) ?: '',
            true
        );
        // @TODO expires after should be a parameter
        $item->set(json_encode($definition))->expiresAfter(60 * 60 * 24);
        $this->cache->save($item);

        return $definition;
    }
}
