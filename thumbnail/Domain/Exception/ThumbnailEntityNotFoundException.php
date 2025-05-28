<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Domain\Exception;

class ThumbnailEntityNotFoundException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, 404);
    }
}
