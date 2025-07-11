<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Presentation\Event\Transport\GetEntity;

/**
 * @template T of object
 */
class RetrieveEntityFunctor
{
    /**
     * @param GetEntity<T> $event
     *
     * @return T|null
     */
    public function __invoke(GetEntity $event): ?object
    {
        if (!class_exists($event->class)) {
            return null;
        }

        return $event->repository->find($event->id);
    }
}
