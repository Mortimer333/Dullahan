<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

use Dullahan\Entity\Domain\Exception\EntityClassNotFoundException;

/**
 * @phpstan-type Mappings array<string, array{
 *      prefix: string,
 *  }>
 */
interface MappingsManagerInterface
{
    /**
     * @return Mappings
     */
    public function getMappings(): array;

    /**
     * @return class-string
     *
     * @throws EntityClassNotFoundException
     */
    public function mappingToClassName(string $mappingAlias, string $path): string;
}
