<?php

declare(strict_types=1);

namespace Dullahan\Main\Event\Entity;

use Symfony\Contracts\EventDispatcher\Event;

class PreRemove extends Event
{
    public const NAME = 'dullahan.pre_entity_remove';

    public function __construct(
        protected object $entity
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
