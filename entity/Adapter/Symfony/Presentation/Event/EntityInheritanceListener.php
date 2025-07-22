<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Domain\DefaultAction\HandleInheritanceAwareEntityFunctor;
use Dullahan\Entity\Domain\Mapper\EntityInheritanceMapper;
use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\FillInheritanceAwareEntity;
use Dullahan\Entity\Presentation\Event\Transport\GetEntity;
use Dullahan\Entity\Presentation\Event\Transport\PersistCreatedEntity;
use Dullahan\Entity\Presentation\Event\Transport\PersistUpdatedEntity;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityInheritanceListener
{
    public function __construct(
        protected HandleInheritanceAwareEntityFunctor $handleInheritanceAwareEntityFunctor,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[AsEventListener(event: PersistUpdatedEntity::class, priority: 10)]
    #[AsEventListener(event: PersistCreatedEntity::class, priority: 10)]
    public function setEntityPaths(PersistUpdatedEntity|PersistCreatedEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity instanceof InheritanceAwareInterface) {
            return;
        }

        $this->eventDispatcher->dispatch(new FillInheritanceAwareEntity($entity));
    }

    #[AsEventListener(event: FillInheritanceAwareEntity::class)]
    public function onFillInheritanceAwareEntity(FillInheritanceAwareEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        ($this->handleInheritanceAwareEntityFunctor)($event);
    }

    #[AsEventListener(event: GetEntity::class, priority: -10)]
    public function loadEntityToMapper(GetEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity instanceof InheritanceAwareInterface) {
            return;
        }

        EntityInheritanceMapper::addInheritedParent($entity);
    }

    #[AsEventListener(event: PersistCreatedEntity::class, priority: -10)]
    #[AsEventListener(event: PersistUpdatedEntity::class, priority: -10)]
    public function updateCurrentInheritedParents(PersistCreatedEntity|PersistUpdatedEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity instanceof InheritanceAwareInterface) {
            return;
        }

        $inherited = EntityInheritanceMapper::getCurrentInheritedParents();
        $inherited[$entity::class][(int) $entity->getId()] = $entity->getParent()?->getId();
        EntityInheritanceMapper::setCurrentInheritedParents($inherited);
    }
}
