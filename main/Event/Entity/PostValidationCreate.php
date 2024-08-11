<?php

declare(strict_types=1);

namespace Dullahan\Main\Event\Entity;

class PostValidationCreate
{
    public const NAME = 'dullahan.post_validation_create';

    public function __construct(
        protected object $entity
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
