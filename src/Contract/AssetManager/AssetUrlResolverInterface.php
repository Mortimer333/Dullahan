<?php

declare(strict_types=1);

namespace Dullahan\Contract\AssetManager;

use Dullahan\Exception\AssetManager\AssetPathCannotBeRetrievedException;

interface AssetUrlResolverInterface
{
    /**
     * @throws AssetPathCannotBeRetrievedException
     */
    public function getUrl(AssetInterface $asset): string;

    /**
     * @throws AssetPathCannotBeRetrievedException
     */
    public function getUrlPath(AssetInterface $asset): string;
}
