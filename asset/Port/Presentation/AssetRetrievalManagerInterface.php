<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Exception\AssetNotFoundException;
use Dullahan\Main\Model\Context;

interface AssetRetrievalManagerInterface
{
    public function exists(string $path, ?Context $context = null): bool;

    /**
     * @return array<Asset>
     */
    public function list(?Context $context = null): array;

    /**
     * @throws AssetNotFoundException
     */
    public function get(mixed $id, ?Context $context = null): Asset;

    /**
     * @throws AssetNotFoundException
     */
    public function getByPath(string $path, ?Context $context = null): Asset;
}
