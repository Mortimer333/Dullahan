<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application\Manager;

use Dullahan\User\Domain\Entity\User;

interface UserPersistManagerInterface
{
    /**
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function create(
        string $username,
        #[\SensitiveParameter] string $email,
        #[\SensitiveParameter] string $password,
    ): User;

    //    /**
    //     * @param array<int|string, mixed> $payload
    //     */
    //    public function update(int $id, array $payload): User;
    //
    //    public function remove(int $id): bool;
}
