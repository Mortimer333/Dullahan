<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\Transport\Create;

use Dullahan\Asset\Application\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Application\Port\Presentation\NewStructureInterface;
use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Structure;

final class CreateAssetEvent
{
    protected ?Structure $structure = null;
    protected ?AssetEntityInterface $entity = null;

    public function __construct(
        protected NewStructureInterface $newStructure,
        protected Context $context,
    ) {
    }

    public function getNewStructure(): NewStructureInterface
    {
        return $this->newStructure;
    }

    public function getCreatedStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setCreatedFile(?Structure $structure): void
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

    public function getContext(): Context
    {
        return $this->context;
    }
}
