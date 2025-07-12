<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Doctrine\ORM\Mapping;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Domain\EntityCacheServiceInterface;
use Dullahan\Entity\Presentation\Event\Transport\CacheRemoveRelated;

class CacheRemoveRelatedFunctor
{
    public function __construct(
        protected EntityCacheServiceInterface $entityCacheService,
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
    ) {
    }

    public function __invoke(CacheRemoveRelated $event): void
    {
        $entity = $event->entity;
        $definition = $event->definition;
        foreach ($definition as $name => $column) {
            if (!is_array($column['type'])) {
                continue;
            }

            if (Mapping\ManyToMany::class == $column['relation'] || Mapping\OneToMany::class == $column['relation']) {
                $getter = 'get' . $this->entityDefinitionManager->pluralize($column, $name);
                $related = $entity->$getter();
                foreach ($related as $item) {
                    $this->removeEntityCache($item);
                }
                continue;
            }

            $getter = 'get' . $name;
            $related = $entity->$getter();
            if ($related) {
                $this->removeEntityCache($related);
            }
        }
    }

    public function removeEntityCache(object $entity): void
    {
        $this->entityCacheService->deleteEntityCache($entity, true);
        $this->entityCacheService->deleteEntityCache($entity, false);
    }
}
