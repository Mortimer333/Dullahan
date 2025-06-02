<?php

declare(strict_types=1);

namespace Dullahan\Object\Presentation\Event\Transport;

class PostValidationCreate
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
