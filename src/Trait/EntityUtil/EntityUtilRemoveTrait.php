<?php

declare(strict_types=1);

namespace Dullahan\Trait\EntityUtil;

use Dullahan\Attribute\Asset;
use Dullahan\Contract\AssetAwareInterface;
use Dullahan\Contract\InheritanceAwareInterface;
use Dullahan\Entity\AssetPointer;

trait EntityUtilRemoveTrait
{
    public function removeConjoinedAsset(AssetAwareInterface $entity, string $field, AssetPointer $pointer): void
    {
        $class = $this->getEntityTrueClass($entity);
        $reflectionClass = new \ReflectionClass($class);

        if (!$reflectionClass->hasProperty($field)) {
            throw new \Exception(sprintf('Entity %s is missing %s property', $class, $field), 500);
        }

        $property = $reflectionClass->getProperty($field);
        $assets = $property->getAttributes(Asset::class);
        if (empty($assets)) {
            return;
        }

        /** @var \ReflectionAttribute<Asset> $assetAttr */
        $assetAttr = end($assets);
        /** @var Asset $asset */
        $asset = $assetAttr->newInstance();
        if ($asset->conjoined && $pointer->getAsset()) {
            $this->em->remove($pointer->getAsset());
        }
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
}
