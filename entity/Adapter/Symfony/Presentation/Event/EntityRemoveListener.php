<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Adapter\Symfony\Domain\RemoveEntityFunctor;
use Dullahan\Entity\Presentation\Event\Transport\RemoveEntity;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityRemoveListener
{
    public function __construct(
        protected RemoveEntityFunctor $removeEntityFunctor,
    ) {
    }

    #[AsEventListener(event: RemoveEntity::class)]
    public function onRemoveEntity(RemoveEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        ($this->removeEntityFunctor)($event);
    }
}
