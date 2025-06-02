<?php

declare(strict_types=1);

namespace Dullahan\Object\Adapter\Symfony\Presentation\Event;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Service\CacheService;
use Dullahan\Object\Adapter\Symfony\Domain\Trait\Entity\IndicatorTrait;
use Dullahan\Object\Port\Domain\EntityServiceInterface;

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
