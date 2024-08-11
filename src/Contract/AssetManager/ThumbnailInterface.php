<?php

declare(strict_types=1);

namespace Dullahan\Contract\AssetManager;

use Doctrine\Common\Collections\Collection;
use Dullahan\Entity\AssetThumbnailPointer;

interface ThumbnailInterface
{
    public function getEntity(): ThumbnailInterface;

    public function getId(): ?int;

    /**
     * @return resource|null
     */
    public function getFile();

    public function getAsset(): ?AssetInterface;

    public function setAsset(?AssetInterface $asset): self;

    public function getName(): ?string;

    public function setName(string $name): self;

    public function getWeight(): ?int;

    public function setWeight(int $weight): self;

    public function getSettings(): ?string;

    public function setSettings(string $settings): self;

    /**
     * @return Collection<int, AssetThumbnailPointer>
     */
    public function getAssetPointers(): \Iterator;

    public function addAssetPointer(AssetThumbnailPointer $assetPointer): self;

    public function removeAssetPointer(AssetThumbnailPointer $assetPointer): self;
}
