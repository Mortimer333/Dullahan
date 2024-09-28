<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Port\Presentation;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Thumbnail\Domain\Thumbnail;

/**
 * @phpstan-type PointerSerialized array{
 *      id: int,
 *      src: string,
 *      name: string,
 *      weight: int,
 *      weight_readable: string,
 *      extension: string,
 *      thumbnails: array<string, string>
 * }
 * @phpstan-type ThumbnailSerialized array{
 *     id: int,
 *     src: string,
 *     name: string,
 *     weight: int,
 *     weight_readable: string,
 *     pointers: array<string, array{
 *          id: int,
 *     }>,
 *     dimensions: array{
 *         width: 'auto'|int,
 *         height: 'auto'|int,
 *     }
 * }
 * @phpstan-type AssetSerialized array{
 *      id: int,
 *     name: string,
 *     extension: string,
 *     src: string,
 *     weight: int,
 *     weight_readable: string,
 *     mime_type: string,
 *     thumbnails: array<ThumbnailSerialized>,
 *     pointers_amount: int,
 *     path: string,
 * }
 */
interface AssetSerializerInterface
{
    /**
     * @return AssetSerialized
     */
    public function serialize(Asset $asset): array;

    /**
     * @return ThumbnailSerialized
     */
    public function serializeThumbnail(Thumbnail $thumbnail): array;

    /**
     * @return PointerSerialized
     */
    public function serializePointer(AssetPointerInterface $pointer): array;
}
