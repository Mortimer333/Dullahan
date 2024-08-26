<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Port\Infrastructure;

use Dullahan\Asset\Application\Exception\AssetEntityNotFoundException;
use Dullahan\Asset\Application\Exception\AssetNotFoundException;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Main\Entity\User;

interface AssetPersistenceManagerInterface
{
    /**
     * @throws AssetNotFoundException
     * @throws AssetEntityNotFoundException
     */
    public function get(int $id): AssetEntityInterface;

    /**
     * @throws AssetNotFoundException
     * @throws AssetEntityNotFoundException
     */
    public function getByPath(string $path): AssetEntityInterface;

    public function create(Structure $structure, User $owner): AssetEntityInterface;

    public function update(AssetEntityInterface $asset, Structure $structure): void;

    public function remove(AssetEntityInterface $asset): void;

    /**
     * Persists changes.
     */
    public function flush(): void;

    public function clear(): void;

    public function exists(string $path): bool;
}
