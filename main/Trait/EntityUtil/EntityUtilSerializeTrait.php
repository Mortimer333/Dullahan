<?php

declare(strict_types=1);

namespace Dullahan\Main\Trait\EntityUtil;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Dullahan\Asset\Entity\AssetPointer;
use Dullahan\Main\Contract\InheritanceAwareInterface;
use Dullahan\Main\Doctrine\Mapper\EntityParentMapper;
use Dullahan\Main\Service\CacheService;

trait EntityUtilSerializeTrait
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param array<string, mixed> $fields
     *
     * @return array<string, mixed>
     */
    protected function getFields(object $entity, array $fields): array
    {
        $serialized = [];
        foreach ($fields as $fieldName => $field) {
            $type = $field['type'];

            $value = $entity->{'get' . ucfirst($fieldName)}();
            if (AssetPointer::class === $type) {
                $serialized[$fieldName] = $this->getAsset($entity, $fieldName, $value);
                continue;
            }

            if (!is_array($type)) {
                $serialized[$fieldName] = $this->getInheritedValue($entity, $fieldName, $value);
                continue;
            }

            if ($value instanceof Collection) {
                $serialized[$fieldName] = $this->getCollectionField(
                    $entity,
                    $value,
                    $type,
                    $fieldName
                );

                continue;
            }

            if ($value instanceof \BackedEnum) {
                $serialized[$fieldName] = $value->value;

                continue;
            }

            $value = $this->getInheritedValue($entity, $fieldName, $value);
            if (is_object($value)) {
                $value = $this->cacheService->getEntitySerializedCacheKey($value, $this->inherit);
            }
            $serialized[$fieldName] = $value ? ['__cached' => $value] : null;
        }

        return $serialized;
    }

    /**
     * @return array<string, int|string|array<string, string|null>|null>|null
     */
    protected function getAsset(object $entity, string $fieldName, mixed $value): ?array
    {
        /** @var ?AssetPointer $value */
        $value = $this->getInheritedValue($entity, $fieldName, $value);
        if (!$value || !$value->getAsset()) {
            return null;
        }

        return $this->assetSerializer->serializePointer($value);
    }

    protected function getInheritedValue(object $entity, string $field, mixed $value): mixed
    {
        if (!$entity instanceof InheritanceAwareInterface || !$this->isEmpty($value) || !$this->inherit) {
            return $this->toSimple($value);
        }

        $getter = 'get' . ucfirst($field);
        $parents = $this->retrieveParents($entity);
        foreach (array_reverse($parents) as $parent) {
            $value = $parent->$getter();
            if (!$this->isEmpty($value)) {
                return $this->toSimple($value);
            }
        }

        return null;
    }

    protected function toSimple(mixed $value): mixed
    {
        if (!is_object($value)) {
            return $value;
        }

        return match ($value::class) {
            \DateTime::class => $value->getTimestamp(),
            default => $value,
        };
    }

    /**
     * @return array<InheritanceAwareInterface>
     */
    protected function retrieveParents(InheritanceAwareInterface $entity): array
    {
        $parents = EntityParentMapper::getParents($entity);
        if (!is_null($parents)) {
            return $parents;
        }

        if (is_null($entity->getRelationPath())) {
            return [];
        }

        $path = explode(',', $entity->getRelationPath());
        $parentEntities = $this->em->createQuery('SELECT p FROM ' . $entity::class . ' p WHERE p.id IN (:parentIds)')
            ->setParameter('parentIds', $path)
            ->execute()
        ;

        $parents = [];
        foreach ($path as $parentId) {
            foreach ($parentEntities as $parent) {
                if ($parent->getId() == $parentId) {
                    $parents[] = $parent;
                    break;
                }
            }
        }

        EntityParentMapper::setParents($entity, $parents);

        return $parents;
    }

    /**
     * @param array<string, mixed> $serialized
     * @param array<string, mixed> $dataSet
     *
     * @return array<string, mixed>
     */
    protected function retrieveNecessaryOnly(array $serialized, array $dataSet): array
    {
        $tornOut = [];
        foreach ($dataSet as $fieldName => $field) {
            if (!array_key_exists($fieldName, $serialized)) {
                continue;
            }
            $settings = $this->retrieveSettings($field);

            if (isset($serialized[$fieldName]['__cached'])) {
                $cached = $serialized[$fieldName]['__cached'];
                if (!is_array($field)) {
                    throw new \Exception('Invalid data set for ' . $fieldName . ', expected array', 400);
                }

                if (is_array($cached)) {
                    /** @var array{
                     *     fieldAttr: array<string, mixed>, field: string, entity: class-string, id: int
                     * } $cached
                     */
                    $cached = $cached;
                    $tornOut[$fieldName] = $this->getCachedCollection($cached, $field, $settings);
                } else {
                    $tornOut[$fieldName] = $this->getCachedFields($cached, $field, $settings);
                }
                continue;
            }

            $tornOut[$fieldName] = $serialized[$fieldName];
        }

        return $tornOut;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param array{fieldAttr: array<string, mixed>, field: string, entity: class-string, id: int} $cached
     * @param array<string, mixed>                                                                 $dataSet
     * @param array<string, mixed>                                                                 $settings
     *
     * @return array<array<string, mixed>>
     *
     * @throws \Exception
     */
    protected function getCachedCollection(array $cached, array $dataSet, array $settings): array
    {
        [
            'fieldAttr' => $fieldAttr,
            'field' => $field,
            'entity' => $entity,
            'id' => $id,
        ] = $cached;

        $entity = $this->getRepository($entity)->find($id);
        $getter = 'get' . $field;
        if (!$entity || !method_exists($entity, $getter)) {
            return [];
        }

        $collection = $this->getInheritedValue($entity, $field, $entity->$getter());
        if (!$collection instanceof Collection) {
            return [];
        }

        if ($fieldAttr['order'] ?? false) {
            $criteria = Criteria::create()->orderBy(['id' => $fieldAttr['order']]);
        }

        if (isset($settings['__criteria'])) {
            $criteria ??= Criteria::create();
            $this->validationService->validateDataSetCriteria($settings['__criteria']);
            $criteria->where($criteria->expr()->andX(...$this->arrayToCriteria($settings['__criteria'])));
        }

        if (isset($criteria)) {
            $collection = $collection->matching($criteria);
        }

        if (isset($fieldAttr['limit']) || isset($settings['__max'])) {
            $collection = $collection->slice(0, $settings['__max'] ?? $fieldAttr['limit']);
        }

        $serialized = [];

        foreach ($collection as $child) {
            $serialized[] = $this->serialize($child, $dataSet, $this->inherit);
        }

        return $serialized;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<\Doctrine\Common\Collections\Expr\Expression>
     */
    protected function arrayToCriteria(array $criteria): array
    {
        $expr = Criteria::expr();
        $nested = [
            'andX' => true,
            'orX' => true,
        ];

        $results = [];
        foreach ($criteria as $method => $criterion) {
            if ($nested[$method] ?? null) {
                $results[] = $expr->$method(...$this->arrayToCriteria($criterion));
                continue;
            }

            foreach ($criterion as $item) {
                $results[] = $expr->$method(...$item);
            }
        }

        return $results;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array<string, mixed> $dataSet
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>|null
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getCachedFields(string $cacheKey, array $dataSet, array $settings): ?array
    {
        if (CacheService::NO_CACHE == $cacheKey) {
            return null;
        }

        [,, $class, $id] = explode(':', $cacheKey);

        /** @var class-string $class */
        $class = str_replace('-', '\\', $class);
        $entity = $this->getRepository($class)->find($id);
        if (!$entity) {
            return null;
        }

        return $this->serialize($entity, $dataSet, $this->inherit);
    }

    /**
     * @return array<string, mixed>
     */
    protected function retrieveSettings(mixed &$field): array
    {
        if (!is_array($field)) {
            return [];
        }

        $settings = [];
        foreach ($field as $name => $item) {
            if (!is_string($name) || '__' !== substr($name, 0, 2)) {
                continue;
            }
            $settings[$name] = $item;
            unset($field[$name]);
        }

        return $settings;
    }

    /**
     * @param array<string, mixed>|null $dataSet
     */
    protected function dataSetContains(?array $dataSet, string $key): bool
    {
        return ($dataSet && ($dataSet[$key] ?? false) === 1) || !$dataSet;
    }

    /**
     * @param array<string, mixed>    $type
     * @param Collection<int, object> $value
     *
     * @return array<mixed>
     */
    protected function getCollectionField(
        object $entity,
        Collection $value,
        array $type,
        string $field,
    ): array {
        if ($entity instanceof InheritanceAwareInterface) {
            $empty = $this->emptyIndicatorService->getEmptyIndicator($entity, $field);
            if ($empty) {
                return [];
            }
            $value = $this->getInheritedValue($entity, $field, $value);
        }

        if (is_null($value) || $value->isEmpty()) {
            return [];
        }

        /** @var array{limit: ?int, order: ?string} $fieldAttr */
        $fieldAttr = $type['_field'] ?? throw new \Exception('Invalid field definition', 500);

        return ['__cached' => [
            'fieldAttr' => $fieldAttr,
            'field' => $field,
            'entity' => $this->getEntityTrueClass($entity),
            'id' => method_exists($entity, 'getId') ? $entity->getId() : 0,
        ]];
    }
}
