<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Asset\Domain\Exception\UploadedFileNotAccessibleException;

interface NewStructureInterface
{
    /**
     * @return ?resource
     *
     * @throws UploadedFileNotAccessibleException
     */
    public function getResource();

    public function getSize(): int;

    public function getPath(): string;

    public function getName(): string;

    public function getOriginalName(): string;

    public function getExtension(): ?string;

    public function getMimeType(): ?string;
}
