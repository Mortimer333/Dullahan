<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Exception;

class EntityNotAuthorizedException extends \DomainException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}
