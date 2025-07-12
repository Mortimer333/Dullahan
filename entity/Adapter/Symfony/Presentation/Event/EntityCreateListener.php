<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Domain\DefaultAction\CreateEntityFunctor;
use Dullahan\Entity\Domain\DefaultAction\ValidateEntityCreationFunctor;
use Dullahan\Entity\Presentation\Event\Transport\CreateEntity;
use Dullahan\Entity\Presentation\Event\Transport\ValidateCreateEntity;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityCreateListener
{
    public function __construct(
        protected ValidateEntityCreationFunctor $validateEntityCreationFunctor,
        protected CreateEntityFunctor $createEntityFunctor,
    ) {
    }

    #[AsEventListener(event: ValidateCreateEntity::class)]
    public function onValidateCreateEntity(ValidateCreateEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->isValid = ($this->validateEntityCreationFunctor)($event);
    }

    #[AsEventListener(event: CreateEntity::class)]
    public function onCreateEntity(CreateEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->entity = ($this->createEntityFunctor)($event);
    }
}
