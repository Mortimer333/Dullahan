<?php

declare(strict_types=1);

namespace Dullahan\Main\Exception;

class SagaNotHandledException extends \DomainException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
