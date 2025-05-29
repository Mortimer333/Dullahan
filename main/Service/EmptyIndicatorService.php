<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Contract\InheritanceAwareInterface;
use Dullahan\Main\Event\InheritEmpty;

class EmptyIndicatorService
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    /**
     * @param class-string $entityClass
     */
    public function removeEmptyIndicators(string $entityClass, int $entityId): void
    {
        $this->em->createQuery(
            'DELETE FROM ' . InheritEmpty::class . ' e WHERE 
                e.entityClass = :entityClass
                AND e.entityId = :entityId'
        )
            ->setParameter('entityClass', $entityClass)
            ->setParameter('entityId', $entityId)
            ->execute()
        ;
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    public function setEmptyIndicators(object $entity, array $payload): void
    {
        if (!$entity instanceof InheritanceAwareInterface || !$entity->getId()) {
            return;
        }

        $meta = (array) $this->em->getClassMetadata($entity::class);
        $mappings = $meta['associationMappings'];
        $indicatorChanged = false;
        foreach (array_keys($mappings) as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $indicatorChanged = true;

            if (empty($payload[$field]) && !is_null($payload[$field])) {
                $this->setEmptyIndicator($entity, $field);
            } else {
                $this->removeEmptyIndicator($entity, $field);
            }
        }

        if ($indicatorChanged) {
            $this->em->flush();
        }
    }

    public function getEmptyIndicator(InheritanceAwareInterface $entity, string $field): ?InheritEmpty
    {
        if (!$entity->getId()) {
            return null;
        }

        return $this->em->getRepository(InheritEmpty::class)->findOneBy([
            'entityClass' => $entity::class,
            'entityId' => $entity->getId(),
            'entityField' => $field,
        ]);
    }

    public function setEmptyIndicator(InheritanceAwareInterface $entity, string $field): void
    {
        $empty = $this->getEmptyIndicator($entity, $field);
        if ($empty || !$entity->getId()) {
            return;
        }

        $empty = new InheritEmpty();
        $empty->setEntityId($entity->getId())
            ->setEntityField($field)
            ->setEntityClass($entity::class)
        ;
        $this->em->persist($empty);
    }

    public function removeEmptyIndicator(InheritanceAwareInterface $entity, string $field): void
    {
        $empty = $this->getEmptyIndicator($entity, $field);
        if (!$empty) {
            return;
        }

        $this->em->remove($empty);
    }
}
