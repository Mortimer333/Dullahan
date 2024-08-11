<?php

declare(strict_types=1);

namespace Dullahan\Contract\AssetManager;

use Dullahan\Entity\User;
use Dullahan\Exception\AssetManager\AssetEntityNotFoundException;
use Dullahan\Exception\AssetManager\AssetExistsException;
use Dullahan\Exception\AssetManager\AssetNotFoundException;
use Dullahan\Exception\AssetManager\UploadedFileNotAccessibleException;

interface AssetManagerInterface
{
    /** @TODO Maybe move those three methods to separate interface? Could allow db separation like CQRS */
    /**
     * @throws AssetNotFoundException
     * @throws AssetEntityNotFoundException
     */
    public function get(int $id): AssetInterface;

    /**
     * @throws AssetNotFoundException
     * @throws AssetEntityNotFoundException
     */
    public function getByPath(string $path): AssetInterface;

    public function exists(string $path): bool;

    /**
     * @param string $path  Absolute path to the parent
     * @param ?User  $owner User to be assigned as an owner of the image. If not given, one will be retrieved from
     *                      session
     *
     * @throws AssetExistsException
     * @throws AssetNotFoundException
     */
    public function folder(string $path, string $name, ?User $owner = null): AssetInterface;

    /**
     * @param string $path  Absolute path to the parent
     * @param ?User  $owner User to be assigned as an owner of the image. If not given, one will be retrieved from
     *                      session
     *
     * @throws AssetExistsException
     * @throws AssetNotFoundException
     * @throws UploadedFileNotAccessibleException
     */
    public function upload(
        string $path,
        string $name,
        UploadedFileInterface $file,
        ?User $owner = null,
    ): AssetInterface;

    public function remove(AssetInterface $asset): bool;

    public function dontRemove(AssetInterface $asset): bool;

    /**
     * Move allows for replacing file with different one without creating new entity in DB via $file variable.
     *
     * @throws UploadedFileNotAccessibleException
     */
    public function move(AssetInterface $asset, string $path, ?UploadedFileInterface $file = null): AssetInterface;

    /**
     * Duplicates given asset and returns its copy.
     */
    public function clone(AssetInterface $asset, string $path): AssetInterface;

    /**
     * Persists changes.
     */
    public function flush(): void;

    /**
     * Clears all currently managed object.
     */
    public function clear(): void;
}
