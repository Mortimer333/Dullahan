<?php

declare(strict_types=1);

namespace Dullahan\Object\Port\Domain;

interface EntityValidationInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @throws \Exception
     */
    public function validateDataSetCriteria(array $criteria): void;

    /**
     * @param array<string, mixed> $pagination
     *
     * @throws \Exception Throws when validation is invalid
     */
    public function validatePagination(array $pagination): void;

    /**
     * @param array<int|string, mixed> $payload
     */
    public function handlePreCreateValidation(object $entity, array $payload): void;

    /**
     * @param array<int|string, mixed> $payload
     */
    public function handlePreUpdateValidation(object $entity, array $payload, bool $validateOwner = true): void;
}
