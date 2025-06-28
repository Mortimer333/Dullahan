<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Exception;

class EntityValidationException extends \DomainException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}
