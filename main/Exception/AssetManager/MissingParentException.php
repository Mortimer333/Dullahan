<?php

declare(strict_types=1);

namespace Dullahan\Main\Exception\AssetManager;

class MissingParentException extends \Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('Asset\'s %s parent folder was not found', $path), 404);
    }
}
