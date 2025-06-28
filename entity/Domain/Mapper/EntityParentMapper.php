<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Mapper;

use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;

/**
 * @TODO this is only used by serialized, make it non-static as it gives us no benefits
 */
class EntityParentMapper
{
    /** @var array<class-string, array<int, array<InheritanceAwareInterface>>> */
    protected static array $parents = [];

    /**
     * @return array<InheritanceAwareInterface>|null
     */
    public static function getParents(InheritanceAwareInterface $entity): ?array
    {
        return self::$parents[$entity::class][$entity->getId()] ?? null;
    }

    /**
     * @param array<InheritanceAwareInterface> $parents
     */
    public static function setParents(InheritanceAwareInterface $entity, array $parents): void
    {
        self::$parents[$entity::class][(int) $entity->getId()] = $parents;
    }
}
