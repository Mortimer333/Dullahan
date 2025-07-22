<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Adapter\Symfony\Domain\EmptyIndicatorService;
use Dullahan\Entity\Domain\Exception\InvalidEntityException;
use Dullahan\Entity\Port\Application\EntityCacheManagerInterface;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\PersistCreatedEntity;

class PersistCreatedEntityFunctor
{
    public function __construct(
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected EmptyIndicatorService $emptyIndicatorService, // @TODO Interface
        protected EntityCacheManagerInterface $entityCacheManager,
    ) {
    }

    public function __invoke(PersistCreatedEntity $event): IdentityAwareInterface
    {
        $entity = $event->entity;
        $repository = $this->entityRetrievalManager->getRepository($entity::class);
        if (!$repository) {
            throw new InvalidEntityException('Entity is missing a repository');
        }

        $definition = $this->entityDefinitionManager->getEntityDefinition($entity);
        if (!$definition) {
            throw new InvalidEntityException(
                sprintf('Entity %s is missing definition', $entity::class),
            );
        }

        // This is a little unexplicit: should be flush in event for persisting entity?
        // This option exist to allow multiple actions on multiple entities before actually flushing.
        // This might be a candidate to move to separate event Flush.
        $repository->save($entity, $event->flush);

        if ($entity instanceof InheritanceAwareInterface && $entity->getParent()) {
            $entity->getParent()->addChild($entity);
        }
        $this->emptyIndicatorService->setEmptyIndicators($entity, $event->payload);

        $this->entityCacheManager->removeRelatedCache($entity, $definition);

        return $entity;
    }
}
