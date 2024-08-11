<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Domain;

use DullahanMainContract\AssetManager\AssetPointerInterface;

final readonly class ThumbnailConfig
{
    public function __construct(
        public string $code,
        public ?int $width = null,
        public ?int $height = null,
        public ?bool $autoResize = null,
        public array $crop = [],
        public ?AssetPointerInterface $assetPointer,
    ) {
    }

    public function getFingerPrint(): string
    {
        return md5((string) $this);
    }

    public function __toString(): string
    {
        return json_encode([
            "code" => $this->getCode(),
            "width" => $this->getWidth(),
            "height" => $this->getHeight(),
            "autoResize" => $this->getAutoResize(),
            "crop" => $this->getCrop(),
        ]);
    }
}
