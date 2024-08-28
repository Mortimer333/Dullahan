<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Port\Presentation;

/**
 * @phpstan-import-type AssetSerialized from \Dullahan\Asset\Application\Port\Presentation\AssetSerializerInterface
 */
interface AssetMiddlewareInterface
{
    /**
     * @return AssetSerialized
     */
    public function serialize(int $id): array;

    /**
     * @return AssetSerialized
     */
    public function retrieve(int $id): array;

    /**
     * @param array<mixed> $pagination
     *
     * @return array<AssetSerialized>
     */
    public function list(array $pagination): array;

    /**
     * @param resource $resource
     *
     * @return AssetSerialized
     */
    public function upload(
        string $name,
        string $path,
        $resource,
        string $originalName,
        int $size,
        string $extension,
        string $mimeType,
    ): array;

    /**
     * @param resource $resource
     *
     * @return AssetSerialized
     */
    public function reupload(
        int $id,
        $resource,
        string $originalName,
        int $size,
        string $extension,
        string $mimeType,
    ): array;

    public function remove(int $id): void;
}
