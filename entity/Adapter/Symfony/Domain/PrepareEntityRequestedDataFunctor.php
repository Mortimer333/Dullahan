<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Domain;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Dullahan\Entity\Domain\Normalizers\InheritedValueNormalizer;
use Dullahan\Entity\Domain\Service\EntityCacheService;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Application\EntitySerializerInterface;
use Dullahan\Entity\Port\Domain\EntityValidationInterface;
use Dullahan\Main\Model\Context;

/**
 * @TODO Extensional use of Doctrine Criteria - has to be replaced.
 *      We could get external query builder here. We don't care about
 *      creating entities but only for the actual values, straight from DB.
 *      It would be more performant and would allow for generating more complex SQL's.
 *      Issue would be that we have to commit to one DB - which I'm starting to think
 *      is a correct choice (but maybe we would be able to find query builder with multiple
 *      supported schemas?).
 *
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 */
class PrepareEntityRequestedDataFunctor
{
    public function __construct(
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
        protected EntityValidationInterface $entityValidation,
        protected EntitySerializerInterface $entitySerializer,
        protected InheritedValueNormalizer $inheritedValueNormalizer,
    ) {
    }

    /**
     * @param SerializedEntity     $serialized
     * @param array<string, mixed> $dataSet
     *
     * @return SerializedEntity
     */
    public function __invoke(array $serialized, array $dataSet, bool $inherit): array
    {
        $tornOut = [];
        foreach ($dataSet as $fieldName => $field) {
            if (!array_key_exists($fieldName, $serialized)) {
                continue;
            }

            if (isset($serialized[$fieldName]['__cached'])) {
                $cached = $serialized[$fieldName]['__cached'];
                if (!is_array($field)) {
                    throw new \Exception('Invalid data set for ' . $fieldName . ', expected array', 400);
                }

                if (is_array($cached)) {
                    $settings = $this->spliceSettings($field);
                    /** @var array{
                     *     fieldAttr: array<string, mixed>,
                     *     field: string,
                     *     entity: class-string<T>,
                     *     id: int
                     * } $cached
                     */
                    $cached = $cached;
                    $tornOut[$fieldName] = $this->getCachedCollection($cached, $field, $settings, $inherit);
                } else {
                    $tornOut[$fieldName] = $this->getCachedFields($cached, $field, $inherit);
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
     * @param array{fieldAttr: array<string, mixed>, field: string, entity: class-string<T>, id: int} $cached
     * @param array<string, mixed>                                                                    $dataSet
     * @param array<string, mixed>                                                                    $settings
     *
     * @return array<array<string, mixed>|null>
     *
     * @throws \Exception
     */
    protected function getCachedCollection(array $cached, array $dataSet, array $settings, bool $inherit): array
    {
        [
            'fieldAttr' => $fieldAttr,
            'field' => $field,
            'entity' => $entity,
            'id' => $id,
        ] = $cached;

        $entity = $this->entityRetrievalManager->getRepository($entity)?->find($id);

        $getter = 'get' . $field;
        if (!$entity || !method_exists($entity, $getter)) {
            return [];
        }
        $collection = $entity->$getter();
        if ($inherit) {
            // @TODO move inherit functionality to separate service (with specific interface)
            $definition = $this->entityDefinitionManager->getEntityDefinition($entity);
            if ($definition && isset($definition[$field])) {
                $collection = $this->inheritedValueNormalizer->normalize(
                    $field,
                    $collection,
                    $definition[$field],
                    $entity,
                    (new Context())->setProperty('inherit', $inherit),
                );
            }
        }
        if (!$collection instanceof Collection) {
            return [];
        }

        if ($fieldAttr['order'] ?? false) {
            $criteria = Criteria::create()->orderBy(['id' => $fieldAttr['order']]);
        }

        if (isset($settings['__criteria'])) {
            $criteria ??= Criteria::create();
            $this->entityValidation->validateDataSetCriteria($settings['__criteria']);
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
            $serialized[] = $this->entitySerializer->serialize(
                $child,
                $dataSet,
                $inherit,
            );
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
     *
     * @return array<string, mixed>|null
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getCachedFields(string $cacheKey, array $dataSet, bool $inherit): ?array
    {
        if (EntityCacheService::NO_CACHE == $cacheKey) {
            return null;
        }

        // @TODO cache key to class should be handled by EntityCacheService
        [,, $class, $id] = explode(':', $cacheKey);

        /** @var class-string $class */
        $class = str_replace('-', '\\', $class);
        $entity = $this->entityRetrievalManager->getRepository($class)?->find($id);
        if (!$entity) {
            return null;
        }

        return $this->entitySerializer->serialize($entity, $dataSet, $inherit);
    }

    /**
     * @param array<string, mixed> $field
     *
     * @return array<string, mixed>
     */
    protected function spliceSettings(array &$field): array
    {
        $settings = [];
        foreach ($field as $name => $item) {
            if (!str_starts_with($name, '__')) {
                continue;
            }
            $settings[$name] = $item;
            unset($field[$name]);
        }

        return $settings;
    }
}
