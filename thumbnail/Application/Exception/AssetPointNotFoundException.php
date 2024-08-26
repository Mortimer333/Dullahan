<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Exception;

class AssetPointNotFoundException extends \Exception
{
    public function __construct(int $pointerId)
    {
        parent::__construct(sprintf('Pointer [%s] was not found', $pointerId), 404);
    }
}
