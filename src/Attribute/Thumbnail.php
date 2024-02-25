<?php

declare(strict_types=1);

namespace Dullahan\Attribute;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PROPERTY)]
class Thumbnail
{
    /**
     * @param array<int|string|null> $crop
     */
    public function __construct(
        public string $code,
        public ?int $width = null,
        public ?int $height = null,
        public ?bool $autoResize = null,
        public array $crop = [],
    ) {
        if (!is_int($this->height) && !is_int($this->width)) {
            throw new \Exception('Thumbnail Attribute is missing width or height', 500);
        }

        if (!empty($this->crop) && count($this->crop) < 2) {
            throw new \Exception('Cropping an image requires at least two parameters', 500);
        }

        if (!empty($this->crop)) {
            $this->crop[0] = (int) $this->crop[0];                  // width
            $this->crop[1] = (int) $this->crop[1];                  // height
            $this->crop[2] = (int) ($this->crop[2] ?? 0);           // X translation
            $this->crop[3] = (int) ($this->crop[3] ?? 0);           // Y translation
            $this->crop[4] = (string) ($this->crop[4] ?? 'MM');     // Starting position - Middle Middle
        }
    }
}
