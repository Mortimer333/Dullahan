<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Exception\AssetPathCannotBeRetrievedException;

interface AssetUrlResolverInterface
{
    /**
     * @throws AssetPathCannotBeRetrievedException
     */
    public function getUrl(Asset $asset): string;

    /**
     * @throws AssetPathCannotBeRetrievedException
     */
    public function getUrlPath(Asset $asset): string;
}
