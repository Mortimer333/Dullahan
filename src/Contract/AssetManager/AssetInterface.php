<?php

declare(strict_types=1);

namespace Dullahan\Contract\AssetManager;

use Dullahan\Entity\AssetPointer;
use Dullahan\Entity\Thumbnail;
use Dullahan\Entity\User;
use Dullahan\Exception\AssetManager\PropertyNotRemovedException;
use Dullahan\Exception\AssetManager\PropertyNotSetException;

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
     * @return \IteratorAggregate<int, AssetPointer>
     */
    public function getPointers(): \IteratorAggregate;
    public function addPointer(AssetPointer $pointer): self;
    public function removePointer(AssetPointer $pointer): self;

    /**
     * @return \IteratorAggregate<int, Thumbnail>
     */
    public function getThumbnails(): \IteratorAggregate;
    public function addThumbnail(ThumbnailInterface $thumbnail): self;
    public function removeThumbnail(ThumbnailInterface $thumbnail): self;

    public function getCreated(): ?\DateTimeInterface;
    public function createdBy(): ?User;

    public function getModified(): ?\DateTimeInterface;
    public function setModified(\DateTimeInterface $modified): self;
    public function modifiedBy(): ?User;

    /**
     * @return \IteratorAggregate<string, mixed>
     */
    public function getProperties(): \IteratorAggregate;
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
     * @return \IteratorAggregate<AssetInterface>
     */
    public function getChildren(string $match): \IteratorAggregate;
}
