<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Port\Presentation;

use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;

interface ThumbnailMapperInterface
{
    /**
     * @return array<ThumbnailConfig>
     */
    public function mapField(AssetAwareInterface $entity, string $fieldName): array;

    /**
     * @return ThumbnailConfig
     */
    public function mapEntity(AssetAwareInterface $entity): array;
}
