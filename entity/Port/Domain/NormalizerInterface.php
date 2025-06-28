<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

use Dullahan\Main\Model\Context;

/**
 * @phpstan-import-type EntityFieldDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
interface NormalizerInterface
{
    /**
     * @param EntityFieldDefinition $definition
     */
    public function normalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): mixed;

    /**
     * @param EntityFieldDefinition $definition
     */
    public function canNormalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): bool;
}
