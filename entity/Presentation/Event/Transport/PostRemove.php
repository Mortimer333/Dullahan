<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

class PostRemove
{
    public function __construct(
        protected object $entity
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
