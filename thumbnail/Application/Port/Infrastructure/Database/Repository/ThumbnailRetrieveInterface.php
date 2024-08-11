<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository;

use DullahanMainContract\AssetManager\AssetInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailConfigInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailInterface;

interface ThumbnailRetrieveInterface
{
    /**
     * Finds already existing Thumbnails on the same asset and same settings. Used to determinate if new thumbnail must
     * be created or if we can reuse already existing one
     */
    public function findSame(AssetInterface $asset, ThumbnailConfigInterface $config): ThumbnailInterface|null;
}
