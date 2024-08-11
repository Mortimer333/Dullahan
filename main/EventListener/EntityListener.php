<?php

declare(strict_types=1);

namespace Dullahan\Main\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Asset\Manager\FileSystemBasedAssetManager;
use Dullahan\Main\Service\CacheService;
use Dullahan\Main\Service\Util\EntityUtilService;
use Dullahan\Main\Trait\Listener;

class EntityListener
{
    use Listener\Entity\AssetTrait;
    use Listener\Entity\IndicatorTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected EntityUtilService $entityUtilService,
        protected FileSystemBasedAssetManager $assetService,
        protected CacheService $cacheService,
    ) {
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
