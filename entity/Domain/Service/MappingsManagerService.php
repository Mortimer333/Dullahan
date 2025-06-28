<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Service;

use Dullahan\Entity\Domain\Exception\EntityClassNotFoundException;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;

/**
 * @phpstan-import-type Mappings from \Dullahan\Entity\Port\Domain\MappingsManagerInterface
 */
class MappingsManagerService implements MappingsManagerInterface
{
    /**
     * @param Mappings $mappings
     */
    public function __construct(
        protected array $mappings
    ) {
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function mappingToClassName(string $mappingAlias, string $path): string
    {
        $prefix = $this->getMappings()[$mappingAlias]['prefix'] ?? '';
        $mapping = rtrim($prefix, '\\') . '\\' . $path;

        if (!class_exists($mapping)) {
            throw new EntityClassNotFoundException(
                'Invalid mapping or path given, selected entity definition doesn\'t exist',
            );
        }

        return $mapping;
    }
}
