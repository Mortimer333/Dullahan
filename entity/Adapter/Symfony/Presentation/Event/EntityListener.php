<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Adapter\Symfony\Domain\Trait\Entity\IndicatorTrait;
use Dullahan\Entity\Port\Domain\EntityServiceInterface;
use Dullahan\Main\Service\CacheService;

class EntityListener
{
    use IndicatorTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected EntityServiceInterface $entityUtilService,
        protected CacheService $cacheService,
    ) {
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
