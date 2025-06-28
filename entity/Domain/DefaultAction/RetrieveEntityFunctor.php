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
        $class = $event->class;
        $id = $event->id;
        $repository = $event->repository;
        if (!class_exists($class)) {
            return null;
        }

        return $repository->find($id);
    }
}
