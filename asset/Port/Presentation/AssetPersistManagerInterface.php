<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Exception\AssetNotClonedException;
use Dullahan\Asset\Domain\Exception\AssetNotCreatedException;
use Dullahan\Asset\Domain\Exception\AssetNotMovedException;
use Dullahan\Asset\Domain\Exception\AssetNotReplacedException;
use Dullahan\Main\Model\Context;

interface AssetPersistManagerInterface
{
    /**
     * @throws AssetNotCreatedException
     */
    public function create(NewStructureInterface $file, ?Context $context = null): Asset;

    /**
     * @throws AssetNotMovedException
     */
    public function move(Asset $asset, string $path, ?Context $context = null): Asset;

    /**
     * @throws AssetNotReplacedException
     */
    public function replace(Asset $asset, NewStructureInterface $file, ?Context $context = null): Asset;

    public function remove(Asset $asset, ?Context $context = null): void;

    /**
     * @throws AssetNotClonedException
     */
    public function clone(Asset $asset, string $path, ?Context $context = null): Asset;
}
