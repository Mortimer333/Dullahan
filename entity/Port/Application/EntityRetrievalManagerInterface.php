<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Application;

use Dullahan\Entity\Domain\Exception\EntityNotAuthorizedException;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;

interface EntityRetrievalManagerInterface
{
    /**
     * @param class-string $class
     *
     * @throws EntityNotAuthorizedException
     */
    public function get(string $class, int $id): ?IdentityAwareInterface;

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return EntityRepositoryInterface<T>
     */
    public function getRepository(string $class): ?EntityRepositoryInterface;
}
