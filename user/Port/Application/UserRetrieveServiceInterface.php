<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application;

use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Domain\Exception\UserNotFoundException;
use Dullahan\User\Domain\Exception\UserNotLoggedInException;

/**
 * @phpstan-type SerializedUser array{
 *      id: int|null,
 *      email: string|null,
 *      data: array<string, mixed>,
 *      storage?: array{
 *           readable: array{
 *               limit: string,
 *               limit: string,
 *           },
 *           limit: int,
 *           taken: int,
 *      }
 *   }
 * @phpstan-type SerializedUserData array{id: int|null, name: string|null}
 */
interface UserRetrieveServiceInterface
{
    /**
     * @throws UserNotFoundException
     */
    public function get(int $id): User;

    /**
     * @throws UserNotLoggedInException
     */
    public function getLoggedInUser(): User;

    public function isLoggedIn(): bool;

    /**
     * @return SerializedUser
     */
    public function serialize(User $user): array;

    /**
     * @return SerializedUserData
     */
    public function serializeData(UserData $data): array;
}
