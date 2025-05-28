<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Port\Infrastructure\Database\Repository;

use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Port\Presentation\AssetPointerInterface;
use Dullahan\Thumbnail\Domain\Exception\ThumbnailEntityNotFoundException;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailEntityInterface;

interface ThumbnailRetrieveInterface
{
    /**
     * Finds already existing Thumbnails on the same asset and same settings. Used to determinate if new thumbnail must
     * be created or if we can reuse already existing one.
     */
    public function findSame(int $assetId, ThumbnailConfig $config): ?ThumbnailEntityInterface;

    public function exists(string $path): bool;

    /**
     * @return array<ThumbnailEntityInterface>
     */
    public function getThumbnails(AssetEntityInterface $assetEntity): array;

    /**
     * @return array<ThumbnailEntityInterface>
     */
    public function getThumbnailsByPointer(AssetPointerInterface $pointer): array;

    /**
     * @throws ThumbnailEntityNotFoundException
     */
    public function get(int $id): ThumbnailEntityInterface;

    /**
     * @throws ThumbnailEntityNotFoundException
     */
    public function getByPath(string $path): ThumbnailEntityInterface;
}
