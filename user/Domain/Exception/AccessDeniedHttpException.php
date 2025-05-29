<?php

declare(strict_types=1);

namespace Dullahan\User\Domain\Exception;

class AccessDeniedHttpException extends \Exception
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
