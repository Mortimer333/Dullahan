<?php

declare(strict_types=1);

namespace Dullahan\Exception\AssetManager;

class AssetPathCannotBeRetrievedException extends \Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('URL for path %s couldn\'t be generated', $path), 500);
    }
}
