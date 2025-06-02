<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

class PostCreate
{
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
