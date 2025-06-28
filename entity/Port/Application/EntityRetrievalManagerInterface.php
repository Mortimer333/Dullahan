<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Application;

use Dullahan\Entity\Domain\Exception\EntityNotAuthorizedException;
use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;

/**
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 */
interface EntityRetrievalManagerInterface
{
    /**
     * @param class-string<T> $class
     *
     * @return T
     *
     * @template T of object
     *
     * @throws EntityNotAuthorizedException
     */
    public function get(string $class, int $id): ?object;

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return EntityRepositoryInterface<T>
     */
    public function getRepository(string $class): ?EntityRepositoryInterface;

    /**
     * @template T of object
     *
     * @param T                         $entity
     * @param array<string, mixed>|null $dataSet
     *
     * @return SerializedEntity|null
     */
    public function serialize(
        object $entity,
        ?array $dataSet = null,
        bool $inherit = true
    ): ?array;
}
