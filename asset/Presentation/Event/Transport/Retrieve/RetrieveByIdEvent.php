<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Retrieve;

use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Main\Model\Context;

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
