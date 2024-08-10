<?php

declare(strict_types=1);

namespace Dullahan\Asset;

use Dullahan\Contract\AssetManager\UploadedFileInterface;
use Dullahan\Exception\AssetManager\UploadedFileNotAccessibleException;
use Symfony\Component\HttpFoundation\File\UploadedFile as BaseUploadedFile;

class UploadedFile implements UploadedFileInterface
{
    public function __construct(
        protected BaseUploadedFile $file,
    ) {
    }

    public function getResource()
    {
        $resource = fopen($this->file->getRealPath(), 'r');
        if (!$resource) {
            throw new UploadedFileNotAccessibleException();
        }

        return $resource;
    }

    public function getSize(): int
    {
        return (int) $this->file->getSize();
    }

    public function getExtension(): string
    {
        return (string) $this->file->guessExtension();
    }

    public function getMimeType(): string
    {
        return (string) $this->file->getMimeType();
    }
}
