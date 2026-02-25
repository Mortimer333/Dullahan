<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application\Manager;

use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Exception\UserNotFoundException;
use Dullahan\User\Domain\Exception\UserNotLoggedInException;

interface UserRetrievalManagerInterface
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
}
