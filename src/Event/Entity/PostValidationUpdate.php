<?php

declare(strict_types=1);

namespace Dullahan\Event\Entity;

class PostValidationUpdate
{
    public const NAME = 'dullahan.post_validation_update';

    public function __construct(
        protected object $entity
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
