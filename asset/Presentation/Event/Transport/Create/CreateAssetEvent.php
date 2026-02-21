<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Create;

use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Port\Presentation\NewStructureInterface;
use Dullahan\Main\Model\Context;

final class CreateAssetEvent
{
    private ?Structure $structure = null;
    private ?AssetEntityInterface $entity = null;

    public function __construct(
        private NewStructureInterface $newStructure,
        private Context $context,
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
