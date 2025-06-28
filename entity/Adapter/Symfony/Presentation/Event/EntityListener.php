<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Adapter\Symfony\Domain\Trait\Entity\IndicatorTrait;
use Dullahan\Entity\Domain\Service\EntityCacheService;
use Dullahan\Entity\Port\Domain\EntityServiceInterface;

class EntityListener
{
    use IndicatorTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected EntityServiceInterface $entityUtilService,
        protected EntityCacheService $cacheService,
    ) {
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
