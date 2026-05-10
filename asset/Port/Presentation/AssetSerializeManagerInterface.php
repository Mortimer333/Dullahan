<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Main\Model\Context;

/**
 * @phpstan-type AssetSerialized array{
 *      id: int,
 *      name: string,
 *      extension: string,
 *      src: string,
 *      weight: int,
 *      weight_readable: string,
 *      mime_type: string,
 *      pointers_amount: int,
 *      path: string,
 *  }
 * @phpstan-type PointerSerialized array{
 *       id: int,
 *       src: string,
 *       name: string,
 *       weight: int,
 *       weight_readable: string,
 *       extension: string,
 *  }
 */
interface AssetSerializeManagerInterface
{
    /**
     * @return AssetSerialized
     */
    public function serialize(Asset $asset, ?Context $context = null): array;

    /**
     * @return PointerSerialized
     */
    public function serializePointer(AssetPointer $pointer, ?Context $context = null): array;
}
