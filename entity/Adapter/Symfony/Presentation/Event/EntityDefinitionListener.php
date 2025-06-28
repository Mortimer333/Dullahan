<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Domain\DefaultAction\GetEntityDefinitionFunctor;
use Dullahan\Entity\Presentation\Event\Transport\GetEntityDefinition;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityDefinitionListener
{
    public function __construct(
        protected GetEntityDefinitionFunctor $getEntityDefinition,
    ) {
    }

    #[AsEventListener(event: GetEntityDefinition::class)]
    public function onGetEntityDefinition(GetEntityDefinition $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->definition = ($this->getEntityDefinition)($event->entity);
    }
}
