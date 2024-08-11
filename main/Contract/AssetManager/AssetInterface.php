<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract\AssetManager;

use Dullahan\Main\Entity\AssetPointer;
use Dullahan\Main\Entity\User;
use Dullahan\Main\Exception\AssetManager\PropertyNotRemovedException;
use Dullahan\Main\Exception\AssetManager\PropertyNotSetException;
use Dullahan\Main\Thumbnail\Adapter\Infrastructure\Database\Entity\Thumbnail;
use Thumbnail\Application\Port\Presentation\ThumbnailInterface;

interface AssetInterface
{
    public function getOwner(): ?User;

    public function getEntity(): AssetInterface;

    public function getId(): ?int;

    public function getPath(): ?string;

    public function getName(): ?string;

    public function getExtension(): ?string;

    public function getMimeType(): ?string;

    public function getWeight(): ?int;

    /**
     * @return resource|null
     */
    public function getFile();

    /**
     * @return \Countable&\IteratorAggregate<int, AssetPointer>
     */
    public function getPointers(): \IteratorAggregate&\Countable;

    public function addPointer(AssetPointer $pointer): self;

    public function removePointer(AssetPointer $pointer): self;

//    /**
//     * @return \Countable&\IteratorAggregate<int, Thumbnail>
//     */
//    public function getThumbnails(): \IteratorAggregate&\Countable;
//
//    public function addThumbnail(ThumbnailInterface $thumbnail): self;
//
//    public function removeThumbnail(ThumbnailInterface $thumbnail): self;

    public function getCreated(): ?\DateTimeInterface;

    public function createdBy(): ?User;

    public function getModified(): ?\DateTimeInterface;

    public function setModified(\DateTimeInterface $modified): self;

    public function modifiedBy(): ?User;

    /**
     * @return \Countable&\IteratorAggregate<string, mixed>
     */
    public function getProperties(): \IteratorAggregate&\Countable;

    public function getProperty(string $name, mixed $default = null): mixed;

    /**
     * @throws PropertyNotSetException
     */
    public function setProperty(string $name, mixed $value): self;

    /**
     * @throws PropertyNotRemovedException
     */
    public function removeProperty(string $name): self;

    public function getParent(): ?AssetInterface;

    /**
     * @return \Countable&\IteratorAggregate<AssetInterface>
     */
    public function getChildren(string $match): \IteratorAggregate&\Countable;
}
