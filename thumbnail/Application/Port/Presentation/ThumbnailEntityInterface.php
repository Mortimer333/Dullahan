<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Thumbnail\Domain\Entity\AssetThumbnailPointer;

interface ThumbnailEntityInterface
{
    public function getId(): ?int;

    public function getAsset(): ?Asset;

    public function getPath(): ?string;

    public function getSettings(): ?string;

    /**
     * @return \Traversable<AssetThumbnailPointer>&\Countable
     */
    public function getAssetPointers(): \Traversable&\Countable;

    public function addAssetPointer(AssetThumbnailPointer $assetPointer): self;

    public function removeAssetPointer(AssetThumbnailPointer $assetPointer): self;
}
