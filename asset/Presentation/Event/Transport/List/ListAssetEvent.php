<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\List;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Main\Model\Context;

final class ListAssetEvent
{
    /** @var array<Asset> */
    protected array $assets = [];
    protected int $total = 0;

    public function __construct(
        private Context $context,
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return array<Asset>
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * @param array<Asset> $assets
     */
    public function setAssets(array $assets): void
    {
        $this->assets = $assets;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }
}
