<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract\AssetManager;

use Dullahan\Main\Exception\AssetManager\AssetPathCannotBeRetrievedException;

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
