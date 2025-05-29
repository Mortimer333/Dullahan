<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Infrastructure;

use Dullahan\Asset\Port\Presentation\AssetPointerInterface;
use Dullahan\User\Domain\Entity\User;

interface AssetEntityInterface
{
    public function getId(): mixed;

    public function getFullPath(): string;

    public function getOwner(): ?User;

    /**
     * @return \IteratorAggregate<AssetPointerInterface>&\Countable
     */
    public function getPointers(): \IteratorAggregate&\Countable;

    public function addPointer(AssetPointerInterface $pointer): self;

    public function removePointer(AssetPointerInterface $pointer): self;

    public function getModified(): ?\DateTimeInterface;

    public function setModified(\DateTimeInterface $modified): self;
}
