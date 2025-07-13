<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Domain\DefaultAction\UpdateEntityFunctor;
use Dullahan\Entity\Domain\DefaultAction\ValidateEntityUpdateFunctor;
use Dullahan\Entity\Presentation\Event\Transport\UpdateEntity;
use Dullahan\Entity\Presentation\Event\Transport\ValidateUpdateEntity;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityUpdateListener
{
    public function __construct(
        protected ValidateEntityUpdateFunctor $validateEntityUpdateFunctor,
        protected UpdateEntityFunctor $updateEntityFunctor,
    ) {
    }

    #[AsEventListener(event: ValidateUpdateEntity::class)]
    public function onValidateCreateEntity(ValidateUpdateEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->isValid = ($this->validateEntityUpdateFunctor)($event);
    }

    #[AsEventListener(event: UpdateEntity::class)]
    public function onCreateEntity(UpdateEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->entity = ($this->updateEntityFunctor)($event);
    }
}
