<?php

declare(strict_types=1);

namespace Dullahan\Event\Entity;

class Retrieval
{
    public function __construct(
        protected object $entity,
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
