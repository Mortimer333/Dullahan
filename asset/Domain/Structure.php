<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain;

final class Structure
{
    /** @var ?resource */
    private $resource;

    public function __construct(
        readonly public string $path,
        readonly public string $name,
        readonly public StructureTypeEnum $type,
        readonly public ?string $extension = null,
        readonly public ?string $mimeType = null,
        readonly public ?int $weight = null,
    ) {
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
