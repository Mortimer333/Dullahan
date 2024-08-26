<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Domain;

final readonly class ThumbnailConfig
{
    /**
     * @param array<mixed> $crop
     */
    public function __construct(
        public string $code,
        public int $pointerId,
        public int $assetId,
        public ?int $width = null,
        public ?int $height = null,
        public ?bool $autoResize = null,
        public array $crop = [],
    ) {
    }

    public function getFingerPrint(): string
    {
        return md5((string) $this);
    }

    public function __toString(): string
    {
        return json_encode([
            'code' => $this->code,
            'width' => $this->width,
            'height' => $this->height,
            'autoResize' => $this->autoResize,
            'crop' => $this->crop,
        ]) ?: '';
    }
}
