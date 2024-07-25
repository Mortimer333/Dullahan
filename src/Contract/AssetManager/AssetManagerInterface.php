<?php

declare(strict_types=1);

namespace Dullahan\Contract\AssetManager;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface AssetManagerInterface
{
    /**
     * @throws \Exception Not Found
     */
    public function get(int $id): AssetInterface;

    /**
     * @throws \Exception Not Found
     */
    public function getByPath(string $path): AssetInterface;

    /**
     * @param string $path Path should be an absolute path to parent
     * @param array<string, mixed> $properties Additional properties of the image (internal meta)
     */
    public function upload(string $path, string $name, UploadedFile $file, array $properties = []): AssetInterface;

    public function reupload(AssetInterface $asset, UploadedFile $file): AssetInterface;

    public function exists(string $path): bool;

    public function remove(AssetInterface $asset): bool;

    public function move(AssetInterface $asset, string $path): AssetInterface;

    /**
     * Duplicates given asset and returns its copy
     */
    public function duplicate(AssetInterface $asset, string $path): AssetInterface;

    /**
     * Persists changes
     */
    public function flush(): void;
}
