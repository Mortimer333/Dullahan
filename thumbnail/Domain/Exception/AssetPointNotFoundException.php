<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Domain\Exception;

class AssetPointNotFoundException extends \Exception
{
    public function __construct(int $pointerId)
    {
        parent::__construct(sprintf('Pointer [%s] was not found', $pointerId), 404);
    }
}
