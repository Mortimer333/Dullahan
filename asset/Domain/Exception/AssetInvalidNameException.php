<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain\Exception;

class AssetInvalidNameException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Asset "%s" is an invalid name', $name), 422);
    }
}
