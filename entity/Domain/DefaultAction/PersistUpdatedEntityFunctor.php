<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Adapter\Symfony\Domain\EmptyIndicatorService;
use Dullahan\Entity\Domain\Exception\InvalidEntityException;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\EntityCacheServiceInterface;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\PersistUpdatedEntity;

class PersistUpdatedEntityFunctor
{
    public function __construct(
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected EmptyIndicatorService $emptyIndicatorService, // @TODO Interface
        protected EntityCacheServiceInterface $entityCacheService,
    ) {
    }

    public function __invoke(PersistUpdatedEntity $event): IdentityAwareInterface
    {
        $entity = $event->entity;
        $repository = $this->entityRetrievalManager->getRepository($entity::class);
        if (!$repository) {
            throw new InvalidEntityException('Entity is missing a repository');
        }

        $repository->save($entity, $event->flush);

        if ($entity instanceof InheritanceAwareInterface && $entity->getParent()) {
            $entity->getParent()->addChild($entity);
        }
        $this->emptyIndicatorService->setEmptyIndicators($entity, $event->payload);

        $this->entityCacheService->deleteEntityCache($entity, true);
        $this->entityCacheService->deleteEntityCache($entity, false);

        return $entity;
    }
}
