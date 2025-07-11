<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Adapter\Symfony\Domain\Reader\FieldReader;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Domain\EntityCacheServiceInterface;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @phpstan-import-type EntityDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class GetEntityDefinitionFunctor
{
    public function __construct(
        protected EntityCacheServiceInterface $entityCacheService,
        protected CacheItemPoolInterface $cache,
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
        protected EventDispatcherInterface $eventDispatcherInterface,
    ) {
    }

    /**
     * @TODO this should be decoupled into three phases: Cache Retrieve, Generate definition, Cache save
     *
     * @return ?EntityDefinition
     */
    public function __invoke(object $entity): ?array
    {
        $trueClassName = $this->entityDefinitionManager->getEntityTrueClass($entity);
        if (!$trueClassName) {
            return null;
        }

        // @TODO Why is this encoded and decoded at the same time?
        //      Answer - I think it was to make sure that there are no objects when returned?
        $definition = json_decode(
            json_encode(
                // @TODO what about refactoring this to more like Symfony Serializer? It would be great if we could
                //      pass our own reader modules or replace default one
                (new FieldReader($entity, $this->entityCacheService, $this->cache))
                    ->getFields($trueClassName)
            ) ?: '',
            true
        );

        return $definition;
    }
}
