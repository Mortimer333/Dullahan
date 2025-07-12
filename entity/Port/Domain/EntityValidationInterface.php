<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

use Dullahan\Entity\Domain\Exception\EntityNotAuthorizedException;
use Dullahan\Entity\Domain\Exception\EntityValidationException;
use Dullahan\Entity\Domain\Exception\InvalidEntityException;

interface EntityValidationInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @throws EntityValidationException
     */
    public function validateDataSetCriteria(array $criteria): void;

    /**
     * @param array<string, mixed> $pagination
     *
     * @throws EntityValidationException Throws when validation is invalid
     */
    public function validatePagination(array $pagination): void;

    /**
     * @param array<int|string, mixed> $payload
     *
     * @throws \InvalidArgumentException
     */
    public function isCreatePayloadValid(string $entity, array $payload): bool;

    /**
     * @param array<int|string, mixed> $payload
     *
     * @throws \InvalidArgumentException
     * @throws InvalidEntityException
     * @throws EntityNotAuthorizedException
     */
    public function isUpdatePayloadValid(object $entity, array $payload, bool $validateOwner = true): bool;
}
