<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Port\Presentation;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Exception\AssetNotFoundException;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Asset\Port\Presentation\AssetPointerInterface;
use Dullahan\Thumbnail\Domain\Exception\ThumbnailEntityNotFoundException;
use Dullahan\Thumbnail\Domain\Thumbnail;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;

interface ThumbnailServiceInterface
{
    /**
     * @throws ThumbnailEntityNotFoundException
     * @throws AssetNotFoundException
     */
    public function get(mixed $id): Thumbnail;

    /**
     * @throws ThumbnailEntityNotFoundException
     * @throws AssetNotFoundException
     */
    public function getByPath(string $path): Thumbnail;

    /**
     * @return array<Thumbnail>
     */
    public function getThumbnails(Asset $asset): array;

    /**
     * @return array<Thumbnail>
     */
    public function getThumbnailsByPointer(AssetPointerInterface $pointer): array;

    /**
     * @param string $fieldName Name of the field containing AssePointer for the thumbnails to generate
     *
     * @return array<Thumbnail>
     */
    public function generate(AssetAwareInterface $asset, string $fieldName): array;

    public function generateWithConfig(ThumbnailConfig $config): ?Thumbnail;

    public function flush(): void;
}
