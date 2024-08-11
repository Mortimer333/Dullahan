<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

use Dullahan\Thumbnail\Application\Exception\ThumbnailPathCannotBeResolvedException;

interface ThumbnailUrlResolverInterface
{
    /**
     * @throws ThumbnailPathCannotBeResolvedException
     */
    public function getUrl(ThumbnailInterface $thumbnail): string;

    /**
     * @throws ThumbnailPathCannotBeResolvedException
     */
    public function getUrlPath(ThumbnailInterface $thumbnail): string;
}
