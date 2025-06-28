<?php

declare(strict_types=1);

namespace Dullahan\User\Domain\Exception;

class UserNotLoggedInException extends \DomainException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}
