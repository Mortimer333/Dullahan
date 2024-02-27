<?php

declare(strict_types=1);

namespace Dullahan\Doctrine\Mapper;

use Dullahan\Entity\AssetPointer;
use Dullahan\Contract\AssetAwareInterface;

abstract class EntityPointersMapper
{
    /** @var array<class-string, array<int, array<string, AssetPointer>>> */
    protected static array $activePointers = [];

    /**
     * @return array<class-string, array<int, array<string, AssetPointer>>>
     */
    public static function getActivePointers(): array
    {
        return self::$activePointers;
    }

    /**
     * @param array<class-string, array<int, array<string, AssetPointer>>> $pointers
     */
    public static function setActivePointers(array $pointers): void
    {
        self::$activePointers = $pointers;
    }

    /**
     * @return array<string, AssetPointer>
     */
    public static function getEntityActivePointers(AssetAwareInterface $entity): array
    {
        return self::$activePointers[$entity::class][$entity->getId()] ?? [];
    }

    public static function setActivePointer(AssetAwareInterface $entity, string $fieldName): void
    {
        $pointers = self::$activePointers;
        $getter = 'get' . ucfirst($fieldName);
        $pointer = $entity->$getter();
        if (!$pointer) {
            return;
        }

        if (!isset($pointers[$entity::class])) {
            $pointers[$entity::class] = [];
        }

        $id = (int) $entity->getId();
        if (!isset($pointers[$entity::class][$id])) {
            $pointers[$entity::class][$id] = [];
        }

        $pointers[$entity::class][$id][$fieldName] = $pointer;

        self::$activePointers = $pointers;
    }
}
