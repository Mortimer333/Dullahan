<?php

declare(strict_types=1);

namespace Dullahan\Object\Adapter\Symfony\Domain\Reader;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Main\Service\CacheService;
use Dullahan\Object\Domain\Attribute\Field;

// @TODO Asset move

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FieldReader
{
    protected string $rootClass;

    /**
     * @param array<class-string, mixed> $skipType
     */
    public function __construct(
        protected object $root,
        protected CacheService $cacheService,
        protected array $skipType = [],
    ) {
        $this->rootClass = $root::class;
    }

    /**
     * @param class-string              $class
     * @param array<class-string, bool> $skip
     *
     * @return array<string, array<string, mixed>|null>
     */
    public function getFields(
        string $class,
        array $skip = [],
    ): array {
        $fields = $this->getPublicFields($class, $skip);

        return $fields;
    }

    /**
     * @param class-string              $class
     * @param array<class-string, bool> $skip
     *
     * @return array<string, array<string, mixed>>
     */
    public function getPublicFields(string $class, array $skip = []): array
    {
        $this->skipType[$class] = false;
        $reflectionClass = new \ReflectionClass($class);

        $fields = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $name = $property->getName();
            $field = $this->getPublicField($name, $property, $class, $skip);
            if (!$field) {
                continue;
            }
            $fields[$name] = $field;
        }
        $this->skipType[$class] = $fields;

        return $fields;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param class-string              $class
     * @param array<class-string, bool> $skip
     *
     * @return array<string, mixed>
     */
    protected function getPublicField(
        string $name,
        \ReflectionProperty $property,
        string $class,
        array $skip,
    ): ?array {
        if ($skip[$name] ?? false) {
            return null;
        }

        $attributes = $property->getAttributes(Field::class);
        if (empty($attributes)) {
            return null;
        }

        $attribute = end($attributes);
        /** @var Field $field */
        $field = $attribute->newInstance();

        $type = $field->type ?: $this->getReflectionType($property);
        if ($type instanceof \UnitEnum && property_exists($type, 'value')) {
            $type = $type->value;
        }

        // @TODO Specific behaviour for asset package - give an option to handle specific cases
        if (
            AssetPointer::class !== $type
            && (class_exists($type) || Collection::class === $type)
        ) {
            if (Collection::class === $type) {
                $relation = $property->getAttributes(ORM\OneToMany::class);
                if (empty($relation)) {
                    $relation = $property->getAttributes(ORM\ManyToMany::class);
                    if (empty($relation)) {
                        throw new \Exception('Collection used without OneToMany or ManyToMany relation', 500);
                    }
                }
                $relation = end($relation);
                /** @var ?class-string $type */
                $type = $relation->newInstance()->targetEntity;
                if (!$type) {
                    throw new \Exception(sprintf('Relation in %s is missing the target', $name), 500);
                }
            }
            $type = $this->getNestedPublicFields($type, $property, $class, $field);
            if (!$type) {
                return null;
            }
        }

        $field->type = $type;

        return (array) $field;
    }

    protected function getReflectionType(\ReflectionProperty $property): string
    {
        $type = $property->getType();
        if (!$type instanceof \ReflectionNamedType) {
            throw new \Exception(
                sprintf('Reflection type class is not handled - %s', $type ? $type::class : 'null'),
                500,
            );
        }

        return $type->getName();
    }

    /**
     * @param class-string $type
     * @param class-string $class
     *
     * @return array<mixed>|null
     */
    protected function getNestedPublicFields(
        string $type,
        \ReflectionProperty $property,
        string $class,
        Field $field,
    ): ?array {
        $cache = $this->cacheService->getCache();
        $item = $cache->getItem($this->cacheService->getEntityFieldCacheKey($type));
        if ($item->isHit()) {
            return json_decode($item->get(), true);
        }

        $type = $property->getType();
        if (!$type instanceof \ReflectionNamedType) {
            throw new \Exception('Property Type must by named', 500);
        }

        if (Collection::class !== $type->getName()) {
            $res = [
                '_field' => array_merge((array) $field, ['hint' => $type->getName()]),
            ];
            $item->set(json_encode($res));
            $cache->save($item);

            return $res;
        }

        if (empty($field->relation)) {
            throw new \Exception(
                sprintf('Missing relation definition for %s on %s', $class, $property->getName()),
                500
            );
        }

        $attributes = $property->getAttributes($field->relation);
        if (empty($attributes)) {
            throw new \Exception(
                sprintf(
                    'Relation definition %s not found for %s on %s',
                    $field->relation,
                    $class,
                    $property->getName()
                ),
                500
            );
        }

        $attribute = end($attributes);
        $relation = $attribute->newInstance();

        $res = match ($relation::class) {
            ORM\ManyToMany::class, ORM\OneToMany::class => $this->getRelationFields($field, $relation),
            default => throw new \Exception(sprintf('Not handled relation %s', $relation::class), 500),
        };

        $item->set(json_encode($res));
        $cache->save($item);

        return $res;
    }

    /**
     * @return array{_field: array<mixed>}|null
     */
    protected function getRelationFields(Field $field, ORM\ManyToMany|ORM\OneToMany $relation): ?array
    {
        if (isset($this->skipType[$relation->targetEntity])) {
            return $this->skipType[$relation->targetEntity] ?: null;
        }

        return [
            '_field' => array_merge((array) $field, ['hint' => $relation->targetEntity]),
        ];
    }
}
