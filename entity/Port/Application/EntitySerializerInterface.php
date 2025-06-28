<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Application;

/**
 * @template T of object
 *
 * @phpstan-type SerializedEntity array<string, mixed>
 */
interface EntitySerializerInterface
{
    /**
     * @param T                         $entity
     * @param array<string, mixed>|null $dataSet
     *
     * @return SerializedEntity|null
     */
    public function serialize(
        object $entity,
        ?array $dataSet = null,
        bool $inherit = true
    ): ?array;
}
