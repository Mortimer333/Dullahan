<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain;

use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;

final readonly class Asset
{
    public function __construct(
        public Structure $structure,
        public AssetEntityInterface $entity,
        public Context $context,
    ) {
    }
}
