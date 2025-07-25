<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Port\Presentation;

use Dullahan\Asset\Port\Presentation\AssetPointerInterface;
use Dullahan\Thumbnail\Domain\Entity\Thumbnail;

interface ThumbnailPointerInterface
{
    public function getAssetPointer(): ?AssetPointerInterface;

    public function setAssetPointer(?AssetPointerInterface $assetPointer): self;

    public function getThumbnail(): ?ThumbnailEntityInterface;

    public function setThumbnail(?Thumbnail $thumbnail): self;

    public function getCode(): ?string;

    public function setCode(string $code): self;
}
