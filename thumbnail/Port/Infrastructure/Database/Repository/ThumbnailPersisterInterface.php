<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Port\Infrastructure\Database\Repository;

use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Port\Presentation\AssetPointerInterface;
use Dullahan\Thumbnail\Domain\Exception\AssetPointNotFoundException;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailEntityInterface;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailPointerInterface;

interface ThumbnailPersisterInterface
{
    /**
     * @throws AssetPointNotFoundException
     * @throws \InvalidArgumentException
     */
    public function createPointer(
        ThumbnailEntityInterface $thumbnail,
        int $pointerId,
        string $code,
    ): ThumbnailPointerInterface;

    /**
     * @param resource $thumbFileHandle
     */
    public function create(
        AssetEntityInterface $asset,
        string $path,
        string $filename,
        $thumbFileHandle,
        ThumbnailConfig $config,
    ): ThumbnailEntityInterface;

    public function removeThumbnailsFromPointer(AssetPointerInterface $pointer): void;

    public function flush(): void;
}
