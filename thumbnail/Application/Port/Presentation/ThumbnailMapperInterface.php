<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

use Dullahan\Asset\Application\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;

interface ThumbnailMapperInterface
{
    /**
     * @return array<ThumbnailConfig>
     */
    public function mapField(AssetAwareInterface $entity, string $fieldName): array;

    /**
     * @return array<string, array<ThumbnailConfig>>
     */
    public function mapEntity(AssetAwareInterface $entity): array;
}
