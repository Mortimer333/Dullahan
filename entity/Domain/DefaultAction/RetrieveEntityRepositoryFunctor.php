<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;
use Dullahan\Entity\Presentation\Event\Transport\GetEntityRepository;
use Dullahan\Main\Contract\DatabaseActionsInterface;

/**
 * @template T of object
 */
class RetrieveEntityRepositoryFunctor
{
    public function __construct(
        protected DatabaseActionsInterface $databaseConnection,
    ) {
    }

    /**
     * @param GetEntityRepository<T> $event
     *
     * @return EntityRepositoryInterface<T>|null
     */
    public function __invoke(GetEntityRepository $event): ?EntityRepositoryInterface
    {
        $class = $event->class;
        if (!class_exists($class)) {
            return null;
        }

        return $this->databaseConnection->getRepository($class);
    }
}
