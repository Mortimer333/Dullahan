<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application\Manager;

use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Domain\Exception\UserNotFoundException;
use Dullahan\User\Domain\Exception\UserNotLoggedInException;

/**
 * @phpstan-import-type SerializedUser from \Dullahan\User\Port\Application\UserRetrieveServiceInterface
 * @phpstan-import-type SerializedUserData from \Dullahan\User\Port\Application\UserRetrieveServiceInterface
 */
interface UserSerializerInterface
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
