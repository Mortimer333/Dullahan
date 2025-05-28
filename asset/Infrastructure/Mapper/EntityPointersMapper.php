<?php

declare(strict_types=1);

namespace Dullahan\Asset\Infrastructure\Mapper;

use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;

/**
 * @TODO What's the point of this being static?
 */
abstract class EntityPointersMapper
{
    /** @var AssetPointer */
    protected static array $activePointers = [];

    /**
     * @return AssetPointer
     */
    public static function getActivePointers(): array
    {
        return self::$activePointers;
    }

    /**
     * @param AssetPointer $pointers
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
