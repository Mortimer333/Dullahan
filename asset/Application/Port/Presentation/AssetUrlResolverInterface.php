<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Port\Presentation;

use Dullahan\Asset\Application\Exception\AssetPathCannotBeRetrievedException;
use Dullahan\Asset\Domain\Asset;

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
