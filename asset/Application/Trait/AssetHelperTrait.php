<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Trait;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use ICanBoogie\Inflector;

/**
 * @TODO Figure out where this should go and how to make it easier to test (ie. ICanBoogie\Inflector initialization)
 */
trait AssetHelperTrait
{
    public function setAsset(string $column, Asset $asset): self
    {
        if (!$this instanceof AssetAwareInterface) {
            throw new \Exception(
                sprintf('To use Asset Pointers class %s must implement %s', static::class, AssetAwareInterface::class),
                500
            );
        }

        $property = new \ReflectionProperty($this, $column);
        if (
            !$property->getType() instanceof \ReflectionNamedType
            || AssetPointer::class !== $property->getType()->getName()
        ) {
            throw new \Exception(
                sprintf('Chosen property %s is not configured for assets on %s', $column, static::class),
                500
            );
        }

        $attributes = $property->getAttributes(ORM\OneToOne::class);
        if (empty($attributes)) {
            throw new \Exception(
                sprintf('Chosen property %s must implement OneToOne relation', $column),
                500
            );
        }

        $pointer = new AssetPointer();
        $pointer->setAsset($asset)
            ->setEntity($this, $column)
        ;
        $this->$column = $pointer;

        return $this;
    }

    public function addAsset(string $column, Asset $asset): self
    {
        if (!$this instanceof AssetAwareInterface) {
            throw new \Exception(
                sprintf('To use Asset Pointers class %s must implement %s', static::class, AssetAwareInterface::class),
                500
            );
        }

        $property = new \ReflectionProperty($this, $column);
        if (
            !$property->getType() instanceof \ReflectionNamedType
            || Collection::class !== $property->getType()->getName()
        ) {
            throw new \Exception(
                sprintf('Chosen property %s is not configured for assets on %s', $column, static::class),
                500
            );
        }

        $attributes = $property->getAttributes(ORM\OneToMany::class);
        if (empty($attributes)) {
            throw new \Exception(
                sprintf('Chosen property %s must implement OneToMany relation', $column),
                500
            );
        }

        $adder = 'add' . ucfirst((new Inflector())->singularize($column));
        if (!method_exists($this, $adder)) {
            throw new \Exception(
                sprintf('Expected to find method %s on class %s', $adder, $this::class),
                500
            );
        }

        $pointer = new AssetPointer();
        $pointer->setAsset($asset)
            ->setEntity($this, $column)
        ;

        $this->$adder($pointer);

        return $this;
    }
}
