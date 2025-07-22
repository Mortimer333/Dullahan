<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Trait;

use Dullahan\Entity\Port\Domain\IndicatorAwareInterface;

trait IndicatorMethodsTrait
{
    protected function getLastIndicator(string $class, string $parentColumn, int $parentId): int
    {
        return (int) ($this->getEntityManager()
            ->createQuery('
                SELECT i.indicator FROM ' . $class . ' i
                WHERE i.' . $parentColumn . ' = :parent
                ORDER BY i.indicator DESC
            ')
            ->setMaxResults(1)
            ->setParameter('parent', $parentId)
            ->getScalarResult()[0]['indicator'] ?? 0)
        ;
    }

    /**
     * @return array<int>
     */
    protected function makeSpace(IndicatorAwareInterface $entity, int $indicator): array
    {
        if (!$entity->getParent()) {
            return [];
        }

        $changed = $this->getEntityManager()
            ->createQuery('
                SELECT i.id FROM ' . $entity::class . ' i
                WHERE i.indicator >= :indicator
                AND i.' . $entity->getParentField() . ' = :parent
                AND i != :entity
            ')
            ->setParameter('parent', $entity->getParent())
            ->setParameter('indicator', $indicator)
            ->setParameter('entity', $entity)
            ->execute()
        ;
        $changed = array_column($changed, 'id');

        $this->getEntityManager()
            ->createQuery('
                UPDATE ' . $entity::class . ' i
                SET i.indicator = i.indicator + 1
                WHERE i.id IN (:ids)
            ')
            ->setParameter('ids', $changed)
            ->execute()
        ;

        return $changed;
    }

    /**
     * @return array<int>
     */
    protected function popSpace(IndicatorAwareInterface $entity, int $indicator, ?object $parent = null): array
    {
        if (is_null($parent)) {
            $parent = $entity->getParent();
        }

        if (!$parent) {
            return [];
        }

        $changed = $this->getEntityManager()
            ->createQuery('
                SELECT i.id FROM ' . $entity::class . ' i
                WHERE i.indicator > :indicator
                AND i.' . $entity->getParentField() . ' = :parent
            ')
            ->setParameter('parent', $parent)
            ->setParameter('indicator', $indicator)
            ->execute()
        ;
        $changed = array_column($changed, 'id');

        $this->getEntityManager()
            ->createQuery('
                UPDATE ' . $entity::class . ' i
                SET i.indicator = i.indicator - 1
                WHERE i.id IN (:ids)
            ')
            ->setParameter('ids', $changed)
            ->execute()
        ;

        return $changed;
    }

    /**
     * @return array<int>
     */
    protected function moveIndicator(
        IndicatorAwareInterface $entity,
        int $indicator,
        ?object $parent = null,
        ?int $oldIndicator = null
    ): array {
        if (is_null($parent)) {
            $parent = $entity->getParent();
        }

        if (!$parent) {
            return [];
        }

        $oldIndicator ??= (int) $entity->getIndicator();
        if ($oldIndicator == $indicator) {
            return [];
        }

        $criteria = [
            'indicator' => $indicator,
            $entity->getParentField() => $parent,
        ];
        if (!$this->getEntityManager()->getRepository($entity::class)->findOneBy($criteria)) {
            return [];
        }

        $operator = '+';
        if ($oldIndicator < $indicator) {
            $operator = '-';
        }
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT i.id FROM ' . $entity::class . ' i
                WHERE i.indicator >= :indicatorStart 
                AND i.indicator <= :indicatorStop
                AND i.' . $entity->getParentField() . ' = :parent
                AND i != :entity
            ')
            ->setParameter('parent', $parent)
            ->setParameter('entity', $entity)
        ;
        if ($oldIndicator < $indicator) {
            $query->setParameter('indicatorStart', $oldIndicator + 1)
                ->setParameter('indicatorStop', $indicator);
        } else {
            $query->setParameter('indicatorStart', $indicator)
                ->setParameter('indicatorStop', $oldIndicator - 1);
        }
        $changed = $query->execute();
        $changed = array_column($changed, 'id');

        $this->getEntityManager()
            ->createQuery('
                UPDATE ' . $entity::class . ' i
                SET i.indicator = i.indicator ' . $operator . ' 1
                WHERE i.id IN (:ids)
            ')
            ->setParameter('ids', $changed)
            ->execute()
        ;

        return $changed;
    }
}
