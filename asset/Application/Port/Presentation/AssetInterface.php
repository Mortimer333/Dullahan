<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Port\Presentation;

use Dullahan\Asset\Application\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Main\Entity\User;

/**
 * @deprecated
 */
interface AssetInterface
{
    public function getOwner(): ?User;

    public function getFile(): Structure;

    public function getEntity(): AssetEntityInterface;

    public function getParent(): ?AssetInterface;

    /**
     * @return \Countable&\IteratorAggregate<AssetInterface>
     */
    public function getChildren(string $match): \IteratorAggregate&\Countable;
}
