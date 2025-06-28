<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Mapper;

use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;

abstract class EntityInheritanceMapper
{
    /**
     * Holds map of classes implementing InheritanceAwareInterface and their
     * original parent id, to allow checking if it was changed in the onFlush
     * event and refactoring related children's parent indexes.
     *
     * @var array<class-string, array<int, int|null>>
     */
    protected static array $inherited = [];

    /**
     * @return array<class-string, array<int, int|null>>
     */
    public static function getCurrentInheritedParents(): array
    {
        return self::$inherited;
    }

    /**
     * @param array<class-string, array<int, int|null>> $inherited
     */
    public static function setCurrentInheritedParents(array $inherited): void
    {
        self::$inherited = $inherited;
    }

    public static function addInheritedParent(InheritanceAwareInterface $entity): void
    {
        if (!isset(self::$inherited[$entity::class])) {
            self::$inherited[$entity::class] = [];
        }

        self::$inherited[$entity::class][(int) $entity->getId()] = $entity->getParent()?->getId();
    }

    public static function didParentChange(InheritanceAwareInterface $entity): bool
    {
        $parentId = self::$inherited[$entity::class][$entity->getId()] ?? null;

        return $parentId !== $entity->getParent()?->getId();
    }
}
