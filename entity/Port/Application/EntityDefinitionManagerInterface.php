<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Application;

/**
 * @TODO I dislike how `type` field works - it should only be made of string like types and additional
 *      context attributes - not be an actual object
 *
 * @phpstan-type EntityFieldTypeNested array{ _field: EntityFieldDefinitionHint }
 * @phpstan-type EntityFieldDefinition array{
 *      relation: string,
 *      important: array<class-string>,
 *      order: 'ASC'|'DESC'|null,
 *      limit: int|null,
 *      auto: array<mixed>|null,
 *      plural: string|null,
 *      enum: string|null,
 *      type: mixed|EntityFieldTypeNested,
 * }
 * @phpstan-type EntityFieldDefinitionHint array{
 *      relation: string,
 *      important: array<class-string>,
 *      order: 'ASC'|'DESC'|null,
 *      limit: int|null,
 *      auto: array<mixed>|null,
 *      plural: string|null,
 *      enum: string|null,
 *      hint: class-string,
 *      type: mixed,
 * }
 * @phpstan-type EntityDefinition array<string, EntityFieldDefinition>
 */
interface EntityDefinitionManagerInterface
{
    /**
     * @return EntityDefinition|null
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getEntityDefinition(object $entity): ?array;

    /**
     * @return class-string
     */
    public function getEntityTrueClass(object $entity): ?string;
}
