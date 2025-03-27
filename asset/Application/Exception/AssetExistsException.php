<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Exception;

class AssetExistsException extends \Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('Asset "%s" already exists', $path), 409);
    }
}
