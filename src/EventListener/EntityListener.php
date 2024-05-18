<?php

declare(strict_types=1);

namespace Dullahan\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Service\AssetService;
use Dullahan\Service\CacheService;
use Dullahan\Service\Util\EntityUtilService;
use Dullahan\Trait\Listener;

class EntityListener
{
    use Listener\Entity\AssetTrait;
    use Listener\Entity\IndicatorTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected EntityUtilService $entityUtilService,
        protected AssetService $assetService,
        protected CacheService $cacheService,
    ) {
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
