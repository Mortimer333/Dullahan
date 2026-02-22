<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain;

final class Structure
{
    /** @var ?resource */
    private $resource;

    public function __construct(
        public readonly string $path,
        public readonly string $name,
        public readonly StructureTypeEnum $type,
        public readonly ?string $extension = null,
        public readonly ?string $mimeType = null,
        public readonly ?int $weight = null,
        $resource = null,
    ) {
        if (!is_null($resource)) {
            $this->setResource($resource);
        }
    }

    /**
     * @param resource $handle
     */
    public function setResource($handle): static
    {
        if (!is_resource($handle)) {
            throw new \InvalidArgumentException('The argument to this function must be a file resource.');
        }

        $this->resource = $handle;

        return $this;
    }

    /**
     * @return ?resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
