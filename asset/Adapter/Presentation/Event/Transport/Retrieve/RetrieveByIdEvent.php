<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\Transport\Retrieve;

use Dullahan\Asset\Application\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Structure;

final class RetrieveByIdEvent
{
    private ?Structure $structure = null;
    private ?AssetEntityInterface $entity = null;

    public function __construct(
        private mixed $id,
        private Context $context,
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): void
    {
        $this->structure = $structure;
    }

    public function getEntity(): ?AssetEntityInterface
    {
        return $this->entity;
    }

    public function setEntity(?AssetEntityInterface $entity): void
    {
        $this->entity = $entity;
    }
}
