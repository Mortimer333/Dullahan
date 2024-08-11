<?php

declare(strict_types=1);

namespace Dullahan\Exception\AssetManager;

class AssetNotFoundException extends \Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('Asset at path %s was not found', $path), 404);
    }
}
