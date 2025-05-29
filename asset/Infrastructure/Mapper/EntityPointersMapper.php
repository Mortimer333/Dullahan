<?php

declare(strict_types=1);

namespace Dullahan\Asset\Infrastructure\Mapper;

use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;

/**
 * @TODO What's the point of this being static?
 *
 * @phpstan-type AssertPointerCollection array<string, array<int, array<AssetPointer>>>
 */
abstract class EntityPointersMapper
{
    /** @var AssertPointerCollection */
    protected static array $activePointers = [];

    /**
     * @return AssertPointerCollection
     */
    public static function getActivePointers(): array
    {
        return self::$activePointers;
    }

    /**
     * @param AssertPointerCollection $pointers
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
