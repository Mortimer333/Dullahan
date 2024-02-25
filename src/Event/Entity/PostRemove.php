<?php

declare(strict_types=1);

namespace Dullahan\Event\Entity;

use Symfony\Contracts\EventDispatcher\Event;

class PostRemove extends Event
{
    public const NAME = 'app.post_entity_remove';

    public function __construct(
        protected object $entity
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
