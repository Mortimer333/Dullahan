<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Port\Presentation;

use Dullahan\Main\Contract\AssetAwareInterface;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;

interface ThumbnailGeneratorInterface
{
    /**
     * @param array<ThumbnailConfigInterface> $configs
     * @return resource
     */
    public function generate(AssetAwareInterface $entity, ThumbnailConfig $config);
}
