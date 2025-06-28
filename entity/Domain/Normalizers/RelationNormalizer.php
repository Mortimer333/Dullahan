<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Normalizers;

use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\Context;

/**
 * @template T of object
 *
 * @phpstan-import-type EntityFieldTypeNested from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class RelationNormalizer implements NormalizerInterface
{
    /**
     * @param EntityDefinitionManagerInterface<T> $entityDefinitionManager
     */
    public function __construct(
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
    ) {
    }

    /**
     * @param \Countable $value
     * @param T          $entity
     */
    public function normalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): array|string|int|float|bool|\ArrayObject|null {
        /** @var EntityFieldTypeNested $type */
        $type = $definition['type'];

        return $this->getCollectionField(
            $entity,
            $value,
            $type,
            $fieldName,
        );
    }

    public function canNormalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): bool {
        return $value instanceof \Countable && $definition['relation'] && isset($definition['type']['_field']);
    }

    /**
     * @param T                     $entity
     * @param EntityFieldTypeNested $type
     *
     * @return array<mixed>
     */
    protected function getCollectionField(
        object $entity,
        ?\Countable $value,
        array $type,
        string $field,
    ): array {
        // @TODO should be properly handled by InheritValueNormalizer
        //        if ($entity instanceof InheritanceAwareInterface) {
        //            $empty = $this->emptyIndicatorService->getEmptyIndicator($entity, $field);
        //            if ($empty) {
        //                return [];
        //            }
        //            $value = $this->getInheritedValue($entity, $field, $value);
        //        }

        if (is_null($value) || 0 === count($value)) {
            return [];
        }

        return ['__cached' => [
            'fieldAttr' => $type['_field'],
            'field' => $field,
            'entity' => $this->entityDefinitionManager->getEntityTrueClass($entity),
            'id' => method_exists($entity, 'getId') ? $entity->getId() : null,
        ]];
    }
}
