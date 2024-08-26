<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository;

use Dullahan\Asset\Application\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetPointerInterface;
use Dullahan\Thumbnail\Application\Exception\AssetPointNotFoundException;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailEntityInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailPointerInterface;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;

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
