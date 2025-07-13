<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Domain;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Domain\Exception\InvalidEntityException;
use Dullahan\Entity\Port\Application\EntityCacheManagerInterface;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\RemoveEntity;

class RemoveEntityFunctor
{
    public function __construct(
        protected EntityCacheManagerInterface $entityCacheManager,
        protected EntityManagerInterface $em,
        protected EmptyIndicatorService $emptyIndicatorService, // @TODO Interface
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
    ) {
    }

    public function __invoke(RemoveEntity $event): void
    {
        $entity = $event->entity;
        $definition = $this->entityDefinitionManager->getEntityDefinition($entity);
        if (!$definition) {
            throw new InvalidEntityException(
                sprintf('Entity %s is missing definition', $event::class),
            );
        }
        if ($entity instanceof InheritanceAwareInterface) {
            // @TODO Inheritance part is outside the current scope of work and is left as it was
            $id = $entity->getId();
            $this->removeParent($entity);
            $this->emptyIndicatorService->removeEmptyIndicators($entity::class, (int) $id);
        } else {
            $repository = $this->entityRetrievalManager->getRepository($entity::class);
            if (!$repository) {
                throw new InvalidEntityException('Entity is missing a repository');
            }
            $repository->remove($entity, $event->flush);
        }
        $this->entityCacheManager->removeRelatedCache($entity, $definition);
    }

    protected function removeParent(InheritanceAwareInterface $entity): void
    {
        $ids = $this->retrieveChildrenIds($entity->getChildren());
        $ids[] = (int) $entity->getId();
        $this->removeChildParentRelation($ids, $entity::class);
        $this->removeParentAndChildren($ids, $entity::class);
    }

    /**
     * @param array<int>   $children
     * @param class-string $class
     */
    protected function removeParentAndChildren(array $children, string $class): void
    {
        $query = $this->em->createQuery('SELECT c FROM ' . $class . ' c WHERE c.id IN (:parentIds)')
            ->setParameter('parentIds', $children);

        $batchSize = 200;
        $i = 1;
        foreach ($query->toIterable() as $entity) {
            $this->em->remove($entity);

            ++$i;
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }
        $this->em->flush();
    }

    /**
     * @param array<int>   $children
     * @param class-string $class
     */
    protected function removeChildParentRelation(array $children, string $class): void
    {
        $this->em->createQuery('UPDATE ' . $class . ' c SET c.parent = NULL WHERE c.parent IN (:parentIds)')
            ->setParameter('parentIds', $children)
            ->execute();
    }

    /**
     * @param Collection<int, InheritanceAwareInterface> $children
     *
     * @return array<int>
     */
    protected function retrieveChildrenIds(Collection $children): array
    {
        $ids = [];
        /** @var InheritanceAwareInterface $child */
        foreach ($children as $child) {
            $ids[] = (int) $child->getId();
            $children = $child->getChildren();
            // Detach child to avoid cache overflow
            $this->em->detach($child);

            $ids = array_merge($ids, $this->retrieveChildrenIds($children));
        }

        return $ids;
    }
}
