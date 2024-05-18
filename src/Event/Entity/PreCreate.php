<?php

declare(strict_types=1);

namespace Dullahan\Event\Entity;

use Symfony\Contracts\EventDispatcher\Event;

class PreCreate extends Event
{
    public const NAME = 'dullahan.pre_entity_create';

    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        protected object $entity,
        protected array $payload,
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * @return array<mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array<mixed> $payload
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }
}
