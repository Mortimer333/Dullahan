<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

use Dullahan\Main\Contract\AssetAwareInterface;

interface ThumbnailServiceInterface
{
    /**
     * @param string $fieldName Name of the field containing AssePointer for the thumbnails to generate
     * @return array<ThumbnailInterface>|\Generator<ThumbnailInterface>
     */
    public function generate(AssetAwareInterface $asset, string $fieldName): array|\Generator;
}
