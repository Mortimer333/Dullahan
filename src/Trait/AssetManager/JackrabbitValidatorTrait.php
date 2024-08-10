<?php

declare(strict_types=1);

namespace Dullahan\Trait\AssetManager;

use Dullahan\Contract\AssetManager\AssetInterface;
use Dullahan\Document\JackrabbitAsset;

trait JackrabbitValidatorTrait
{
    protected function validateIsJackrabbitAsset(AssetInterface $asset): void
    {
        if (!($asset instanceof JackrabbitAsset)) {
            throw new \InvalidArgumentException(
                sprintf('Jackrabbit Asset Manager only manages Jackrabbit Assets not %s', $asset::class),
                500,
            );
        }
    }
}
