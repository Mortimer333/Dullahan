<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Mapper;

use Dullahan\Main\Contract\AssetAwareInterface;
use Dullahan\Thumbnail\Application\Attribute\Thumbnail as ThumbnailAttribute;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailMapperInterface;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;
use Thumbnail\Application\Exception\ThumbnailFieldNotMappedException;

class AttributeThumbnailMapper implements ThumbnailMapperInterface
{
    public function mapField(AssetAwareInterface $entity, string $fieldName): array
    {
        try {
            $property = new \ReflectionProperty($entity, $fieldName);
        } catch (\ReflectionException) {
            throw new ThumbnailFieldNotMappedException(
                sprintf('Class "%s" is missing "%s" field', $entity::class, $fieldName),
                500,
            );
        }

        $assets = $property->getAttributes(ThumbnailAttribute::class);


        $configs = [];
        $value = $property->getValue($entity);
        if (!is_null($value) && !($value instanceof AssetAwareInterface)) {
            throw new ThumbnailFieldNotMappedException(
                sprintf(
                    'Class "%s" has invalid value in "%s" field (% or null)',
                    $entity::class,
                    AssetAwareInterface::class,
                ),
                500,
            );
        }
        foreach ($assets as $asset) {
            /** @var ThumbnailAttribute$instance */
            $instance = $asset->newInstance();

            $configs[] = new ThumbnailConfig(
                $instance->getCode(),
                $instance->getWidth(),
                $instance->getHeight(),
                $instance->getAutoResize(),
                $instance->getCrop(),
                $value,
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
