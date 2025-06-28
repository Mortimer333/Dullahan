<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Exception;

class EntityNotFoundException extends \DomainException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
