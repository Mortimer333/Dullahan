<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Domain\DefaultAction\RetrieveEntityFunctor;
use Dullahan\Entity\Domain\DefaultAction\RetrieveEntityRepositoryFunctor;
use Dullahan\Entity\Presentation\Event\Transport\GetEntity;
use Dullahan\Entity\Presentation\Event\Transport\GetEntityRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @template T of object
 */
class EntityRetrievalListener
{
    /**
     * @param RetrieveEntityRepositoryFunctor<T> $retrieveEntityRepository
     * @param RetrieveEntityFunctor<T>           $retrieveEntity
     */
    public function __construct(
        protected RetrieveEntityRepositoryFunctor $retrieveEntityRepository,
        protected RetrieveEntityFunctor $retrieveEntity,
    ) {
    }

    /**
     * @param GetEntity<T> $event
     */
    #[AsEventListener(event: GetEntity::class)]
    public function onGetEntity(GetEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->entity = ($this->retrieveEntity)($event);
    }

    /**
     * @param GetEntityRepository<T> $event
     */
    #[AsEventListener(event: GetEntityRepository::class)]
    public function onGetEntityRepository(GetEntityRepository $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->repository = ($this->retrieveEntityRepository)($event);
    }
}
