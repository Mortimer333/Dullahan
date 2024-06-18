<?php

declare(strict_types=1);

namespace Dullahan\Trait\EntityUtil;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping;
use Doctrine\Persistence\Proxy;
use Dullahan\Contract\InheritanceAwareInterface;
use Dullahan\Contract\ManageableInterface;
use Dullahan\Contract\OwnerlessManageableInterface;
use Dullahan\Entity\User;

trait EntityUtilHelperTrait
{
    /**
     * @return class-string
     */
    public function getEntityTrueClass(object $entity): string
    {
        if (!(class_implements($entity)[Proxy::class] ?? false)) {
            return $entity::class;
        }

        /* @var Proxy $entity */
        return $this->em->getClassMetadata($entity::class)->rootEntityName;
    }

    /**
     * @param class-string $class
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function removeCacheById(int $id, string $class): void
    {
        $this->cacheService->deleteCacheById($id, $class, true);
        $this->cacheService->deleteCacheById($id, $class);
    }

    public function removeEntityCache(object $entity): void
    {
        $this->cacheService->deleteEntityCache($entity, true);
        $this->cacheService->deleteEntityCache($entity, false);
    }

    /**
     * @param array<mixed> $definition
     */
    public function clearRelatedCache(object $entity, array $definition): void
    {
        $inflector = $this->getInflector();
        foreach ($definition as $name => $column) {
            if (!is_array($column['type'])) {
                continue;
            }

            if (Mapping\ManyToMany::class == $column['relation'] || Mapping\OneToMany::class == $column['relation']) {
                $getter = 'get' . $inflector->pluralize($name);
                $related = $entity->$getter();
                foreach ($related as $item) {
                    $this->removeEntityCache($item);
                }
                continue;
            }

            $getter = 'get' . $name;
            $related = $entity->$getter();
            if ($related) {
                $this->removeEntityCache($related);
            }
        }
    }

    public function login(User $user): void
    {
        $this->user = $user;
    }

    public function logout(): void
    {
        $this->user = null;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws \Exception
     */
    protected function generate(string $class): object
    {
        if (!class_exists($class)) {
            throw new \Exception(sprintf("Class %s doesn't exist", $class), 400);
        }

        $implements = class_implements($class);
        if (
            !isset($implements[ManageableInterface::class])
            && !isset($implements[OwnerlessManageableInterface::class])
        ) {
            throw new \Exception(sprintf('Class %s cannot be created, updated or deleted', $class), 400);
        }

        return new $class();
    }

    protected function isEmpty(mixed $value): bool
    {
        return (
            !$value instanceof Collection
            && is_null($value)
        ) || (
            $value instanceof Collection
            && $value->isEmpty()
        );
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
