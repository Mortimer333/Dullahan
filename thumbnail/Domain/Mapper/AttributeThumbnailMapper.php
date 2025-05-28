<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Domain\Mapper;

use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Asset\Port\Presentation\AssetPointerInterface;
use Dullahan\Thumbnail\Domain\Attribute\Thumbnail as ThumbnailAttribute;
use Dullahan\Thumbnail\Domain\Exception\ThumbnailFieldCannotBeMappedException;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailMapperInterface;

class AttributeThumbnailMapper implements ThumbnailMapperInterface
{
    public function mapField(AssetAwareInterface $entity, string $fieldName): array
    {
        try {
            $property = new \ReflectionProperty($entity, $fieldName);
        } catch (\ReflectionException) {
            throw new ThumbnailFieldCannotBeMappedException(
                sprintf('Class "%s" is missing "%s" field', $entity::class, $fieldName),
                500,
            );
        }

        $configs = [];
        $value = $property->getValue($entity);
        if (!is_null($value) && !($value instanceof AssetPointerInterface)) {
            throw new ThumbnailFieldCannotBeMappedException(
                sprintf(
                    'Class "%s" has invalid value in "%s" field (%s or null) (%s)',
                    $entity::class,
                    $fieldName,
                    AssetPointerInterface::class,
                    'object' === gettype($value) ? $value::class : gettype($value),
                ),
                500,
            );
        }

        if (is_null($value) || !$value->getId()) {
            return $configs;
        }

        $assets = $property->getAttributes(ThumbnailAttribute::class);
        foreach ($assets as $assetAttribute) {
            /** @var ThumbnailAttribute $instance */
            $instance = $assetAttribute->newInstance();

            $configs[] = new ThumbnailConfig(
                $instance->getCode(),
                (int) $value->getId(),
                (int) $value->getAsset()?->getId(),
                $instance->getWidth(),
                $instance->getHeight(),
                $instance->getAutoResize(),
                $instance->getCrop(),
            );
        }

        return $configs;
    }

    public function mapEntity(AssetAwareInterface $entity): array
    {
        $reflectionClass = new \ReflectionClass($entity);
        $fields = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes(ThumbnailAttribute::class);
            if (empty($attributes)) {
                continue;
            }

            $fields[$property->getName()] = $this->mapField($entity, $property->getName());
        }

        return $fields;
    }
}
