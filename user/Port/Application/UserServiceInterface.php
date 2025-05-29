<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application;

use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;

interface UserServiceInterface
{
    public function get(int $id): User;

    public function getLoggedInUser(): User;

    public function isLoggedIn(): bool;

    /**
     * @return array{
     *     id: int|null,
     *     email: string|null,
     *     data: array<string, mixed>,
     *     activated: bool,
     *     created: string,
     *     storage: array{
     *          readable: array{
     *              limit: string,
     *              limit: string,
     *          },
     *          limit: int,
     *          taken: int,
     *     }
     *  }
     */
    public function serialize(User $user): array;

    /**
     * @return array{id: int|null, name: string|null}
     */
    public function serializeData(UserData $data): array;

    public function activate(int $id, #[\SensitiveParameter] string $token): void;

    public function deactivate(int $id): void;
}
