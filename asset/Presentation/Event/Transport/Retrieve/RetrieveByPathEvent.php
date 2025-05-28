<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Retrieve;

use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;

final class RetrieveByPathEvent
{
    private ?Structure $structure = null;
    private ?AssetEntityInterface $entity = null;

    public function __construct(
        private string $path,
        private Context $context,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getContext(): Context
    {
        return $this->context;
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
