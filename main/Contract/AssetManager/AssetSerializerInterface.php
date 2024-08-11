<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract\AssetManager;

use Thumbnail\Application\Port\Presentation\ThumbnailInterface;

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
 *          class: string,
 *          column: string,
 *          entity: int,
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
 *     thumbnails: array<ThumbnailSerialized>,
 *     pointers_amount: int,
 * }
 */
interface AssetSerializerInterface
{
    /**
     * @return AssetSerialized
     */
    public function serialize(AssetInterface $asset): array;

    /**
     * @return ThumbnailSerialized
     */
    public function serializeThumbnail(ThumbnailInterface $thumbnail): array;

    /**
     * @return PointerSerialized
     */
    public function serializePointer(AssetPointerInterface $asset): array;
}
