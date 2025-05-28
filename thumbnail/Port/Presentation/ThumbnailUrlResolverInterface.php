<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Port\Presentation;

use Dullahan\Thumbnail\Domain\Exception\ThumbnailPathCannotBeResolvedException;
use Dullahan\Thumbnail\Domain\Thumbnail;

interface ThumbnailUrlResolverInterface
{
    /**
     * @throws ThumbnailPathCannotBeResolvedException
     */
    public function getUrl(Thumbnail $thumbnail): string;

    /**
     * @throws ThumbnailPathCannotBeResolvedException
     */
    public function getUrlPath(Thumbnail $thumbnail): string;
}
