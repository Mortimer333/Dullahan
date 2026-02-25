<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Adapter\Symfony\Presentation\Http\Constraint\DataSetCriteriaConstraint;
use Dullahan\Entity\Adapter\Symfony\Presentation\Http\Constraint\PaginationConstraint;
use Dullahan\Entity\Domain\Exception\EntityNotAuthorizedException;
use Dullahan\Entity\Domain\Exception\EntityValidationException;
use Dullahan\Entity\Domain\Exception\InvalidEntityException;
use Dullahan\Entity\Domain\Reader\EntityReader;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\ConstraintInheritanceAwareInterface;
use Dullahan\Entity\Port\Domain\EntityValidationInterface;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Entity\Port\Domain\ManageableInterface;
use Dullahan\Entity\Port\Domain\OwnerlessManageableInterface;
use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Main\Symfony\SymfonyConstraintValidationService;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityConstraintValidationService extends SymfonyConstraintValidationService implements EntityValidationInterface
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $em,
        protected UserRetrieveServiceInterface $userService,
        protected ErrorCollectorInterface $errorCollector,
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
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
            throw new EntityValidationException('Entity selection has failed');
        }
    }

    public function validatePagination(array $pagination): void
    {
        $this->validate($pagination, PaginationConstraint::get());
        if ($this->errorCollector->hasErrors()) {
            throw new EntityValidationException('Entity pagination has failed');
        }
    }

    /**
     * @param class-string             $entity
     * @param array<int|string, mixed> $payload
     */
    public function isCreatePayloadValid(string $entity, array $payload): bool
    {
        $reader = new EntityReader($entity);
        if ($this->implements($entity, InheritanceAwareInterface::class) && isset($payload['parent'])) {
            if (isset($payload['children'])) {
                throw new \InvalidArgumentException('You cannot assign children with this interface', 400);
            }

            $this->validateChildCreation($reader, $entity, $payload);
        } else {
            $this->runValidationConstraint($payload, $reader->getCreationConstraint(), $entity);
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    public function isUpdatePayloadValid(object $entity, array $payload, bool $validateOwner = true): bool
    {
        if (!$entity instanceof ManageableInterface && !$entity instanceof OwnerlessManageableInterface) {
            throw new InvalidEntityException('Chosen entity cannot be updated');
        }

        if (
            $validateOwner
            && $entity instanceof ManageableInterface
            && !$entity->isOwner($this->userService->getLoggedInUser())
        ) {
            throw new EntityNotAuthorizedException("You cannot update chosen entity as it doesn't belong to you");
        }

        $reader = new EntityReader($entity);
        if ($entity instanceof InheritanceAwareInterface && isset($payload['parent'])) {
            if (isset($payload['children'])) {
                throw new \InvalidArgumentException('You cannot assign children with this interface', 400);
            }

            $this->validateChildUpdate($reader, $entity, $payload);
        } else {
            $this->runValidationConstraint($payload, $reader->getUpdateConstraint(), $entity::class);
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    protected function runValidationConstraint(array &$payload, Assert\Collection $constraint, string $entity): void
    {
        if ($this->implements($entity, InheritanceAwareInterface::class) && array_key_exists('parent', $payload)) {
            $parent = $payload['parent'];
            unset($payload['parent']);
        }

        $this->validate($payload, $constraint);

        if (isset($parent)) {
            $payload['parent'] = $parent;
        }
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    protected function validateChildUpdate(
        EntityReader $reader,
        IdentityAwareInterface $entity,
        array $payload,
    ): void {
        $this->validateConstraintImplementsInheritInterface($reader, $entity::class);
        $this->runValidationConstraint($payload, $reader->getChildUpdateConstraint(), $entity::class);
        $parent = $this->validateParent($entity::class, $payload);

        $path = $parent->getRelationPath();
        if (!is_null($path)) {
            $path = explode(',', $path);
            if (in_array($entity->getId(), $path)) {
                throw new \InvalidArgumentException('Entity cannot have its own child for parent', 400);
            }
        }
    }

    /**
     * @param class-string             $entity
     * @param array<int|string, mixed> $payload
     */
    protected function validateChildCreation(
        EntityReader $reader,
        string $entity,
        array $payload,
    ): void {
        $this->validateConstraintImplementsInheritInterface($reader, $entity);
        $this->runValidationConstraint($payload, $reader->getChildCreationConstraint(), $entity);
        $this->validateParent($entity, $payload);
    }

    protected function validateConstraintImplementsInheritInterface(
        EntityReader $reader,
        string $entity,
    ): void {
        if (!isset(class_implements($reader->getConstraint())[ConstraintInheritanceAwareInterface::class])) {
            throw new InvalidEntityException(
                sprintf(
                    "Constraint of %s doesn't implement %s. Entity validation impossible",
                    $entity,
                    ConstraintInheritanceAwareInterface::class,
                ),
            );
        }
    }

    /**
     * @param class-string             $entity
     * @param array<int|string, mixed> $payload
     */
    protected function validateParent(string $entity, array $payload): InheritanceAwareInterface
    {
        $repository = $this->entityRetrievalManager->getRepository($entity);
        if (!$repository) {
            throw new InvalidEntityException('Chosen entity is missing a repository');
        }

        $parent = $repository->find($payload['parent'] ?? 0);
        if (!$parent) {
            throw new \InvalidArgumentException("Chosen parent for entity wasn't found", 404);
        }

        if (!$parent instanceof InheritanceAwareInterface) {
            throw new \InvalidArgumentException('Chosen entity for parent is not suitable', 422);
        }

        return $parent;
    }

    protected function implements(string|object $entity, string $interface): bool
    {
        return isset(class_implements($entity)[$interface]);
    }
}
