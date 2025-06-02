<?php

declare(strict_types=1);

namespace Dullahan\Object\Port\Interface;

use UnexpectedValueException;

/**
 * Copied from Doctrine\Persistence\ObjectRepository.
 *
 * @template-covariant T of object
 */
interface EntityRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id the identifier
     *
     * @return object|null the object
     *
     * @phpstan-return T|null
     */
    public function find($id);

    /**
     * Finds all objects in the repository.
     *
     * @return array<int, object> the objects
     *
     * @phpstan-return T[]
     */
    public function findAll();

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array<string, mixed>       $criteria
     * @param array<string, string>|null $orderBy
     *
     * @phpstan-param array<string, 'asc'|'desc'|'ASC'|'DESC'>|null $orderBy
     *
     * @return array<int, object> the objects
     *
     * @phpstan-return T[]
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    );

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array<string, mixed> $criteria the criteria
     *
     * @return object|null the object
     *
     * @phpstan-return T|null
     */
    public function findOneBy(array $criteria);

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @phpstan-return class-string<T>
     */
    public function getClassName();
}
