<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Normalizers;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Adapter\Symfony\Domain\EmptyIndicatorService;
use Dullahan\Entity\Domain\Mapper\EntityParentMapper;
use Dullahan\Entity\Domain\Trait\NormalizerHelperTrait;
use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\Context;

/**
 * @phpstan-import-type EntityFieldDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class InheritedValueNormalizer implements NormalizerInterface
{
    use NormalizerHelperTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected EmptyIndicatorService $emptyIndicatorService,
    ) {
    }

    public function normalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): mixed {
        return $this->getInheritedValue($entity, $fieldName, $definition);
    }

    public function canNormalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): bool {
        return $entity instanceof InheritanceAwareInterface
            && $context->getProperty('inherit', false)
            && $this->isEmpty(
                $value,
                $entity,
                $fieldName,
                $definition,
            )
        ;
    }

    /**
     * @param EntityFieldDefinition $definition
     */
    public function getInheritedValue(object $entity, string $fieldName, array $definition): mixed
    {
        if (!$entity instanceof InheritanceAwareInterface) {
            return $this->tryReadField($entity, $fieldName);
        }

        $parents = $this->retrieveParents($entity);
        foreach ($parents as $parent) {
            $value = $this->tryReadField($parent, $fieldName);
            if (!$this->isEmpty($value, $entity, $fieldName, $definition)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param EntityFieldDefinition $definition
     */
    protected function isEmpty(
        mixed $value,
        InheritanceAwareInterface $entity,
        string $fieldName,
        array $definition,
    ): bool {
        if ($definition['relation'] && $this->emptyIndicatorService->getEmptyIndicator($entity, $fieldName)) {
            return true;
        }

        return (
            !$value instanceof \Countable
            && is_null($value)
        ) || (
            $value instanceof \Countable
            && 0 === count($value)
        )
        ;
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

        $path = array_reverse(explode(',', $entity->getRelationPath()));
        // @TODO move it to repository
        // @TODO have separate table for parentIds and relation paths
        $parentEntities = $this->em->createQuery('SELECT p FROM ' . $entity::class . ' p WHERE p.id IN (:parentIds)')
            ->setParameter('parentIds', $path)
            ->execute()
        ;

        // Reorder parent entities, so they are on the same order as in the relation path.
        // Relation path defines who inherits from who in specific order. We have to make
        // sure that we don't skip the closest parents in favour of the further away parents.
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
}
