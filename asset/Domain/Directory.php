<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain;

use Dullahan\Asset\Port\Presentation\NewStructureInterface;

class Directory implements NewStructureInterface
{
    public const LINUX_UBUNTU_FOLDER_SIZE_BYTES = 4000;
    protected string $path;
    protected string $name;

    public function __construct(
        string $path,
    ) {
        $name = explode(DIRECTORY_SEPARATOR, rtrim($path, DIRECTORY_SEPARATOR));
        $this->name = $name[array_key_last($name)];
        $this->path = implode(DIRECTORY_SEPARATOR, array_slice($name, 0, -1)) . DIRECTORY_SEPARATOR;
    }

    public function getResource()
    {
        return null;
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
        return $this->name;
    }

    public function getSize(): int
    {
        return self::LINUX_UBUNTU_FOLDER_SIZE_BYTES;
    }

    public function getExtension(): ?string
    {
        return '';
    }

    public function getMimeType(): ?string
    {
        return 'folder';
    }
}
