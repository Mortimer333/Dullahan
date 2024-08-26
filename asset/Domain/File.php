<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain;

use Dullahan\Asset\Application\Port\Presentation\NewStructureInterface;

class File implements NewStructureInterface
{
    /**
     * @param resource $resource
     */
    public function __construct(
        protected string $path,
        protected string $name,
        protected string $originalName,
        protected $resource,
        protected int $size,
        protected string $extension,
        protected string $mimeType,
    ) {
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}
