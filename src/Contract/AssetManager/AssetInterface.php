<?php

declare(strict_types=1);

namespace Dullahan\Contract\AssetManager;

use Doctrine\Common\Collections\Collection;
use Dullahan\Entity\AssetPointer;
use Dullahan\Entity\Thumbnail;
use Dullahan\Entity\User;

interface AssetInterface
{
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
     * @return Collection<int, AssetPointer>
     */
    public function getPointers(): Collection;
    public function addPointer(AssetPointer $pointer): self;
    public function removePointer(AssetPointer $pointer): self;

    /**
     * @return Collection<int, Thumbnail>
     */
    public function getThumbnails(): Collection;
    public function addThumbnail(Thumbnail $thumbnail): self;
    public function removeThumbnail(Thumbnail $thumbnail): self;

    public function getCreated(): ?\DateTimeInterface;
    public function createdBy(): ?User;

    public function getModified(): ?\DateTimeInterface;
    public function setModified(\DateTimeInterface $modified): self;
    public function modifiedBy(): ?User;

    /**
     * @return array<string, mixed>
     */
    public function getProperties(): \Iterator;
    public function getProperty(string $name, mixed $default = null): mixed;
    public function setProperty(string $name, mixed $value): self;
    public function removeProperty(string $name): self;

    public function markToRemove(bool $remove): bool;
}
