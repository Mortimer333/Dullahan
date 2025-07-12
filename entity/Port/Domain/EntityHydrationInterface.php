<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

interface EntityHydrationInterface
{
    /**
     * @param class-string             $class
     * @param array<int|string, mixed> $payload
     * @param array<string, mixed>     $definitions
     */
    public function hydrate(string $class, object $entity, array $payload, array $definitions): void;
}
