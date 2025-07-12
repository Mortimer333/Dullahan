<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

interface ValidationServiceInterface
{
    /**
     * @param array<mixed> $body
     */
    public function validate(array $body, mixed $constraint): bool;

    public function addViolations(mixed $violations): void;
}
