<?php

declare(strict_types=1);

namespace Dullahan\Main\Reader;

use Dullahan\Main\Attribute\Entity;
use Dullahan\Main\Contract\EntityValidateConstraintInterface;
use Symfony\Component\Validator\Constraints as Assert;

class EntityReader
{
    protected Entity $entity;

    public function __construct(
        protected object $root,
    ) {
        $this->read();
    }

    public function getCreationConstraint(): Assert\Collection
    {
        return $this->entity->constraint::create();
    }

    public function getChildCreationConstraint(): Assert\Collection
    {
        return $this->entity->constraint::createChild();
    }

    public function getUpdateConstraint(): Assert\Collection
    {
        return $this->entity->constraint::update();
    }

    public function getChildUpdateConstraint(): Assert\Collection
    {
        return $this->entity->constraint::updateChild();
    }

    public function getConstraint(): string
    {
        return $this->entity->constraint;
    }

    protected function read(): void
    {
        $reflectionClass = new \ReflectionClass($this->root);
        $attributes = $reflectionClass->getAttributes(Entity::class);
        if (empty($attributes)) {
            throw new \Exception(sprintf('Entity attribute not found on class %s', $this->root::class), 400);
        }

        $attribute = end($attributes);
        $this->entity = $attribute->newInstance();

        if (!class_exists($this->entity->constraint)) {
            throw new \Exception(sprintf("Constraint for class %s doesn't exist", $this->root::class), 500);
        }

        if (!isset(class_implements($this->entity->constraint)[EntityValidateConstraintInterface::class])) {
            throw new \Exception(
                sprintf(
                    "Constraint class %s doesn't implement %s",
                    $this->entity->constraint,
                    EntityValidateConstraintInterface::class,
                ),
                500
            );
        }
    }
}
