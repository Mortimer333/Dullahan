<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Infrastructure;

use Dullahan\Asset\Domain\Exception\AssetEntityNotFoundException;
use Dullahan\Asset\Domain\Exception\AssetNotFoundException;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Main\Entity\User;

// @TODO separate into persistence and retrieval
/**
 * @phpstan-type Sort array<array{
 *     column: string,
 *     direction: 'DESC'|'ASC',
 * }>
 * @phpstan-type Filter array<array{
 *      0: string,
 *      1: '!='|'='|'IS'|'IS NOT'|'<'|'>'|'<>'|'>='|'<='|'LIKE',
 *      2: 'string',
 * }|'AND'|'OR'|'('|')'>
 * @phpstan-type Join array<array{
 *      0: string,
 *      1: string,
 * }>
 * @phpstan-type Group array<string>
 */
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

    /**
     * @param Sort|null   $sort
     * @param Filter|null $filter
     * @param Join|null   $join
     * @param Group|null  $group
     *
     * @return array<AssetEntityInterface>
     */
    public function list(
        int $limit,
        int $offset,
        ?array $sort = null,
        ?array $filter = null,
        ?array $join = null,
        ?array $group = null,
    ): array;

    /**
     * @param Sort|null   $sort
     * @param Filter|null $filter
     * @param Join|null   $join
     * @param Group|null  $group
     */
    public function count(
        ?array $sort = null,
        ?array $filter = null,
        ?array $join = null,
        ?array $group = null,
    ): int;

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
