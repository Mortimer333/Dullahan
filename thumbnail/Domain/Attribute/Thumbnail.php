<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Domain\Attribute;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PROPERTY)]
final class Thumbnail
{
    /**
     * @param array<int|'MM'|'SM'|'EM'|'MS'|'EM'|'SE'|'ES'|'SS'|'EE'> $crop Definition of the crop starting position XY.
     *                                                                      M - Middle, S - Start, E - End
     */
    public function __construct(
        protected string $code,
        protected ?int $width = null,
        protected ?int $height = null,
        protected ?bool $autoResize = null,
        protected array $crop = [],
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
            $this->crop[4] = (string) ($this->crop[4] ?? 'SS');     // @phpstan-ignore-line
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getAutoResize(): ?bool
    {
        return $this->autoResize;
    }

    /**
     * @return array{}|array{
     *      0: int,
     *      1: int,
     *      2: int,
     *      3: int,
     *      4: 'MM'|'SM'|'EM'|'MS'|'EM'|'SE'|'ES'|'SS'|'EE'
     *  }
     */
    public function getCrop(): array
    {
        return $this->crop;
    }
}
