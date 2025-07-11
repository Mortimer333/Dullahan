<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Create;

use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;

final class PostCreateAssetEvent
{
    public function __construct(
        protected Structure $structure,
        protected AssetEntityInterface $entity,
        protected Context $context,
    ) {
    }

    public function getStructure(): Structure
    {
        return $this->structure;
    }

    public function setStructure(Structure $structure): void
    {
        $this->structure = $structure;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getEntity(): AssetEntityInterface
    {
        return $this->entity;
    }

    public function setEntity(AssetEntityInterface $entity): void
    {
        $this->entity = $entity;
    }
}
