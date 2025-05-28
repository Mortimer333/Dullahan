<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Exception\AssetNotClonedException;
use Dullahan\Asset\Domain\Exception\AssetNotCreatedException;
use Dullahan\Asset\Domain\Exception\AssetNotFoundException;
use Dullahan\Asset\Domain\Exception\AssetNotMovedException;
use Dullahan\Asset\Domain\Exception\AssetNotReplacedException;

interface AssetServiceInterface
{
    public function exists(string $path, ?Context $context = null): bool;

    public function validName(string $name, ?Context $context = null): bool;

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
