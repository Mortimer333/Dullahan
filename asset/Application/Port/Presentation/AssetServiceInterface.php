<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Port\Presentation;

use Dullahan\Asset\Application\Exception\AssetNotClonedException;
use Dullahan\Asset\Application\Exception\AssetNotCreatedException;
use Dullahan\Asset\Application\Exception\AssetNotFoundException;
use Dullahan\Asset\Application\Exception\AssetNotMovedException;
use Dullahan\Asset\Application\Exception\AssetNotReplacedException;
use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;

interface AssetServiceInterface
{
    public function exists(string $path, ?Context $context = null): bool;

    /**
     * @return array<Asset>
     */
    public function list(?Context $context = null): array;

    /**
     * @throws AssetNotFoundException
     */
    public function get(mixed $id, ?Context $context = null): Asset;

    /**
     * @throws AssetNotFoundException
     */
    public function getByPath(string $path, ?Context $context = null): Asset;

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

    public function flush(?Context $context = null): void;

    public function clear(?Context $context = null): void;
}
