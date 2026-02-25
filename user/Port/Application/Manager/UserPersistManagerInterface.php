<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application\Manager;

use Dullahan\User\Domain\Entity\User;

interface UserPersistManagerInterface
{
    /**
     * @param array<int|string, mixed> $payload
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function create(array $payload): User;

    //    /**
    //     * @param array<int|string, mixed> $payload
    //     */
    //    public function update(int $id, array $payload): User;
    //
    //    public function remove(int $id): bool;
}
