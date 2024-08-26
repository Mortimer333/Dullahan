<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

use Dullahan\Asset\Application\Port\Presentation\AssetPointerInterface;
use Dullahan\Thumbnail\Entity\Thumbnail;

interface ThumbnailPointerInterface
{
    public function getAssetPointer(): ?AssetPointerInterface;

    public function setAssetPointer(?AssetPointerInterface $assetPointer): self;

    public function getThumbnail(): ?ThumbnailEntityInterface;

    public function setThumbnail(?Thumbnail $thumbnail): self;

    public function getCode(): ?string;

    public function setCode(string $code): self;
}
