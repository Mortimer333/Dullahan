<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract\AssetManager;

use Dullahan\Main\Exception\AssetManager\UploadedFileNotAccessibleException;

interface UploadedFileInterface
{
    /**
     * @return resource
     *
     * @throws UploadedFileNotAccessibleException
     */
    public function getResource();

    public function getSize(): int;

    public function getExtension(): string;

    public function getMimeType(): string;
}
