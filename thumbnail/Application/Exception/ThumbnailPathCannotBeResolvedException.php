<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Exception;

class ThumbnailPathCannotBeResolvedException extends \Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('URL for thumbnail of %s couldn\'t be generated', $path), 500);
    }
}
