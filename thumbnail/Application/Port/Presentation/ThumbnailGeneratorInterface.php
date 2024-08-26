<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

use Dullahan\Thumbnail\Application\Exception\ThumbnailCannotBeGeneratedException;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;

interface ThumbnailGeneratorInterface
{
    /**
     * @return resource
     *
     * @throws ThumbnailCannotBeGeneratedException
     */
    public function generate(ThumbnailConfig $config, string $filename);
}
