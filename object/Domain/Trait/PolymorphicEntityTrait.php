<?php

declare(strict_types=1);

namespace Dullahan\Object\Domain\Trait;

trait PolymorphicEntityTrait // @phpstan-ignore trait.unused
{
    protected ?object $entity = null;

    public function getEntity(): ?object
    {
        if (!$this->entity) {
            if (property_exists($this, 'em')) { // @phpstan-ignore-line
                throw new \Exception(sprintf('Entity %s is missing EntityManager', $this::class), 500);
            }

            $this->entity =
                $this->em->getRepository($this->getEntityClass())
                    ->find((int) $this->getEntityId()); // @phpstan-ignore-line
            if (!$this->entity) {
                throw new \Exception(sprintf('Chosen entity %s was not found', $this->getEntityClass()), 500);
            }
        }

        return $this->entity;
    }

    public function setEntity(object $entity): self
    {
        $this->setEntityClass($entity::class);
        if (!method_exists($entity, 'getId') || !$entity->getId()) {
            throw new \Exception(sprintf('Entity %s is missing ID', $entity::class), 500);
        }
        $this->setEntityId($entity->getId());
        $this->entity = $entity;

        return $this;
    }
}
