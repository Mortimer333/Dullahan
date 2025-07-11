<?php

declare(strict_types=1);

namespace Dullahan\Object\Adapter\Symfony\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Main\Trait\Validate\SymfonyValidationHelperTrait;
use Dullahan\Object\Adapter\Symfony\Domain\Reader\EntityReader;
use Dullahan\Object\Adapter\Symfony\Presentation\Http\Constraint\DataSetCriteriaConstraint;
use Dullahan\Object\Adapter\Symfony\Presentation\Http\Constraint\PaginationConstraint;
use Dullahan\Object\Domain\Contract\ConstraintInheritanceAwareInterface;
use Dullahan\Object\Domain\Contract\InheritanceAwareInterface;
use Dullahan\Object\Port\Domain\EntityValidationInterface;
use Dullahan\User\Port\Application\UserServiceInterface;
use Dullahan\User\Port\Domain\ManageableInterface;
use Dullahan\User\Port\Domain\OwnerlessManageableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityValidationService implements EntityValidationInterface
{
    use SymfonyValidationHelperTrait;

    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService,
        protected ErrorCollectorInterface $errorCollector,
    ) {
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @throws \Exception
     */
    public function validateDataSetCriteria(array $criteria): void
    {
        $this->validate($criteria, DataSetCriteriaConstraint::get());
        if ($this->errorCollector->hasErrors()) {
            throw new \Exception('Entity selection has failed', 400);
        }
    }

    public function validatePagination(array $pagination): void
    {
        $this->validate($pagination, PaginationConstraint::get());
        if ($this->errorCollector->hasErrors()) {
            throw new \Exception('Entity pagination has failed', 400);
        }
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    public function handlePreCreateValidation(object $entity, array $payload): void
    {
        $reader = new EntityReader($entity);
        if ($entity instanceof InheritanceAwareInterface && isset($payload['parent'])) {
            if (isset($payload['children'])) {
                throw new \Exception('You cannot assign children with this interface', 400);
            }

            $this->validateChildCreation($reader, $entity, $payload);
        } else {
            $this->runValidationConstraint($payload, $reader->getCreationConstraint(), $entity);
        }

        if ($this->errorCollector->hasErrors()) {
            throw new \Exception('Entity creation has failed', 400);
        }
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    public function handlePreUpdateValidation(object $entity, array $payload, bool $validateOwner = true): void
    {
        if (!$entity instanceof ManageableInterface && !$entity instanceof OwnerlessManageableInterface) {
            throw new \Exception('Chosen entity cannot be updated', 400);
        }

        if (
            $validateOwner
            && $entity instanceof ManageableInterface
            && !$entity->isOwner($this->userService->getLoggedInUser())
        ) {
            throw new \Exception("You cannot update chosen entity as it doesn't belong to you", 403);
        }

        $reader = new EntityReader($entity);
        if ($entity instanceof InheritanceAwareInterface && isset($payload['parent'])) {
            if (isset($payload['children'])) {
                throw new \Exception('You cannot assign children with this interface', 400);
            }

            $this->validateChildUpdate($reader, $entity, $payload);
        } else {
            $this->runValidationConstraint($payload, $reader->getUpdateConstraint(), $entity);
        }

        if ($this->errorCollector->hasErrors()) {
            throw new \Exception('Entity update has failed', 400);
        }
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    protected function runValidationConstraint(array &$payload, Assert\Collection $constraint, object $entity): void
    {
        if ($entity instanceof InheritanceAwareInterface && array_key_exists('parent', $payload)) {
            $parent['parent'] = $payload['parent'];
            unset($payload['parent']);
        }

        $this->validate($payload, $constraint);

        if (isset($parent)) {
            $payload['parent'] = $parent['parent'];
        }
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    protected function validateChildUpdate(
        EntityReader $reader,
        InheritanceAwareInterface $entity,
        array $payload
    ): void {
        $this->validateConstraintImplementsInheritInterface($reader, $entity);
        $this->runValidationConstraint($payload, $reader->getChildUpdateConstraint(), $entity);
        $parent = $this->validateParent($entity, $payload);

        $path = $parent->getRelationPath();
        if (!is_null($path)) {
            $path = explode(',', $path);
            if (in_array($entity->getId(), $path)) {
                throw new \Exception('Entity cannot have its own child for parent', 400);
            }
        }
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    protected function validateChildCreation(
        EntityReader $reader,
        InheritanceAwareInterface $entity,
        array $payload
    ): void {
        $this->validateConstraintImplementsInheritInterface($reader, $entity);
        $this->runValidationConstraint($payload, $reader->getChildCreationConstraint(), $entity);
        $this->validateParent($entity, $payload);
    }

    protected function validateConstraintImplementsInheritInterface(
        EntityReader $reader,
        InheritanceAwareInterface $entity,
    ): void {
        if (!isset(class_implements($reader->getConstraint())[ConstraintInheritanceAwareInterface::class])) {
            throw new \Exception(
                sprintf(
                    "Constraint of %s doesn't implement %s. Entity validation impossible",
                    $entity::class,
                    ConstraintInheritanceAwareInterface::class,
                ),
                500
            );
        }
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    protected function validateParent(InheritanceAwareInterface $entity, array $payload): InheritanceAwareInterface
    {
        $parent = $this->em->getRepository($entity::class)->find($payload['parent'] ?? 0);
        if (!$parent) {
            throw new \Exception("Chosen parent for entity wasn't found", 404);
        }

        return $parent;
    }
}
