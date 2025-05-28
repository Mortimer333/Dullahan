<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Port\Presentation;

use Dullahan\Thumbnail\Domain\Exception\ThumbnailCannotBeGeneratedException;
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
