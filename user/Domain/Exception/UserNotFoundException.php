<?php

declare(strict_types=1);

namespace Dullahan\User\Domain\Exception;

class UserNotFoundException extends \DomainException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
