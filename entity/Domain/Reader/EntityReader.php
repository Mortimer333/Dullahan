<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Reader;

use Dullahan\Entity\Domain\Attribute\Entity;
use Dullahan\Entity\Port\Domain\ConstraintInheritanceAwareInterface;
use Dullahan\Entity\Port\Domain\EntityValidateConstraintInterface;

class EntityReader
{
    protected Entity $entity;

    /** @var array<string|int, string> */
    protected array $classImplements;

    /**
     * @param object|class-string $root
     *
     * @throws \Exception
     */
    public function __construct(
        protected object|string $root,
    ) {
        $this->read();
    }

    public function getCreationConstraint(): mixed
    {
        return $this->getDefaultConstraint()::create();
    }

    public function getChildCreationConstraint(): mixed
    {
        return $this->getConstraintImplementingInheritance()::createChild();
    }

    public function getUpdateConstraint(): mixed
    {
        return $this->getDefaultConstraint()::update();
    }

    public function getChildUpdateConstraint(): mixed
    {
        return $this->getConstraintImplementingInheritance()::updateChild();
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
            throw new \Exception(sprintf('Entity attribute not found on class %s', $this->rootToString()), 400);
        }

        $attribute = end($attributes);
        $this->entity = $attribute->newInstance();

        if (!class_exists($this->entity->constraint)) {
            throw new \Exception(sprintf("Constraint for class %s doesn't exist", $this->rootToString()), 500);
        }

        $this->classImplements = class_implements($this->entity->constraint);
    }

    /**
     * @return class-string<EntityValidateConstraintInterface>
     *
     * @throws \Exception
     */
    protected function getDefaultConstraint(): string
    {
        if (!isset($this->classImplements[EntityValidateConstraintInterface::class])) {
            throw new \Exception(
                sprintf(
                    "Constraint class %s doesn't implement %s, action is not possible",
                    $this->entity->constraint,
                    EntityValidateConstraintInterface::class,
                ),
                500,
            );
        }

        /** @var class-string<EntityValidateConstraintInterface> $constraint */
        $constraint = $this->entity->constraint;

        return $constraint;
    }

    /**
     * @return class-string<ConstraintInheritanceAwareInterface>
     *
     * @throws \Exception
     */
    protected function getConstraintImplementingInheritance(): string
    {
        if (!isset($this->classImplements[ConstraintInheritanceAwareInterface::class])) {
            throw new \Exception(
                sprintf(
                    "Constraint class %s doesn't implement %s, action is not possible",
                    $this->entity->constraint,
                    ConstraintInheritanceAwareInterface::class,
                ),
                500,
            );
        }

        /** @var class-string<ConstraintInheritanceAwareInterface> $constraint */
        $constraint = $this->entity->constraint;

        return $constraint;
    }

    protected function rootToString(): string
    {
        return is_string($this->root) ? $this->root : $this->root::class;
    }
}
