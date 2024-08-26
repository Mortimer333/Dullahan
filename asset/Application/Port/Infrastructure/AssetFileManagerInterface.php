<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Port\Infrastructure;

use Dullahan\Asset\Application\Exception\AssetExistsException;
use Dullahan\Asset\Application\Exception\AssetNotFoundException;
use Dullahan\Asset\Application\Exception\UploadedFileNotAccessibleException;
use Dullahan\Asset\Application\Port\Presentation\NewStructureInterface;
use Dullahan\Asset\Domain\Structure;

interface AssetFileManagerInterface
{
    public const RECURSIVE = 'recursive';

    /**
     * @throws AssetNotFoundException
     */
    public function get(string $path): Structure;

    public function exists(string $path): bool;

    /**
     * @param string $path Absolute path to the parent
     *
     * @throws AssetExistsException
     * @throws AssetNotFoundException
     */
    public function folder(string $path, string $name): Structure;

    /**
     * @throws AssetExistsException
     * @throws UploadedFileNotAccessibleException
     */
    public function upload(NewStructureInterface $file): Structure;

    public function remove(Structure $asset): bool;

    public function dontRemove(Structure $asset): bool;

    /**
     * Move allows for replacing file with different one without creating new entity in DB via $file variable.
     *
     * @throws AssetExistsException
     */
    public function move(Structure $asset, string $path): Structure;

    /**
     * @throws UploadedFileNotAccessibleException
     * @throws \InvalidArgumentException
     */
    public function reupload(Structure $asset, NewStructureInterface $file): Structure;

    /**
     * Duplicates given asset and returns its copy.
     *
     * @throws AssetExistsException
     */
    public function clone(Structure $asset, string $path): Structure;

    /**
     * Persists changes.
     */
    public function flush(): void;

    /**
     * Clears all currently managed object.
     */
    public function clear(): void;
}