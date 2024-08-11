<?php

declare(strict_types=1);

namespace Dullahan\Contract\AssetManager;

use Dullahan\Exception\AssetManager\AssetThumbnailPathCannotBeRetrievedException;

interface ThumbnailUrlResolverInterface
{
    /**
     * @throws AssetThumbnailPathCannotBeRetrievedException
     */
    public function getUrl(ThumbnailInterface $thumbnail): string;

    /**
     * @throws AssetThumbnailPathCannotBeRetrievedException
     */
    public function getUrlPath(ThumbnailInterface $thumbnail): string;
}
