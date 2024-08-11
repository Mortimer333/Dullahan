<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

use Doctrine\Common\Collections\Collection;
use Dullahan\Main\Contract\AssetManager\AssetInterface;
use Dullahan\Main\Entity\AssetThumbnailPointer;

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
