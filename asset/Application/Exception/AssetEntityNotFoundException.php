<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Exception;

class AssetEntityNotFoundException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, 404);
    }
}
